<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Tek bir üründen sepete eklenebilecek azami adet.
     */
    private const MAX_QUANTITY_PER_ITEM = 100;

    /**
     * Sepet toplamlarını ve geçerli kupon indirimini hesaplar.
     *
     * Sepet, ödeme ve sipariş oluşturma adımlarının TEK hesaplama kaynağıdır.
     * Daha önce bu mantık üç yerde kopyalanmıştı ve her biri kupon limit
     * kontrolüne farklı bir e-posta geçiyordu; bu yüzden sepette görünen
     * indirim ödeme adımında sessizce kaybolabiliyordu.
     *
     * Sepet satırları her okumada veritabanından yeniden kurulur; session yalnızca
     * "hangi üründen kaç adet" bilgisini taşır. Böylece fiyat asla bayatlamaz,
     * silinen ürünler düşer ve stoğu aşan adetler kırpılır.
     *
     * @param  string|null  $email  Misafir kullanıcının ödeme formunda girdiği e-posta.
     * @return array{cart: array, total: float, discount: float, netTotal: float, coupon: array|null, couponDropped: bool, removed: array, adjusted: array}
     */
    private function calculateTotals(?string $email = null): array
    {
        ['cart' => $cart, 'total' => $total, 'removed' => $removed, 'adjusted' => $adjusted] = $this->syncCart();

        $discount      = 0.0;
        $coupon        = null;
        $couponDropped = false;
        $couponSession = session()->get('coupon');

        if ($couponSession) {
            $couponModel = Coupon::where('code', $couponSession['code'])->first();
            $userId      = auth()->id();
            $identity    = $email ?: auth()->user()?->email;

            if ($couponModel && !$couponModel->isExpiredOrLimitReached($userId, $identity)) {
                // İndirim her zaman kupondaki güncel değerden hesaplanır;
                // session'daki kopya bayatlamış olabilir.
                $coupon = [
                    'code'  => $couponModel->code,
                    'type'  => $couponModel->type,
                    'value' => $couponModel->value,
                ];

                $discount = $coupon['type'] === 'percent'
                    ? $total * ($coupon['value'] / 100)
                    : (float) $coupon['value'];

                $discount = min($discount, $total);
            } else {
                session()->forget('coupon');
                $couponDropped = true;
            }
        }

        // İndirim sonrası ara toplam; kargo bunun üzerinden belirlenir.
        $subtotal = max(0, $total - $discount);

        $setting      = Setting::current();
        $shippingCost = $cart ? $setting->shippingCostFor($subtotal) : 0.0;

        return [
            'cart'          => $cart,
            'total'         => $total,
            'discount'      => $discount,
            'subtotal'      => $subtotal,
            'shippingCost'  => $shippingCost,
            'freeShippingRemaining' => $cart ? $setting->remainingForFreeShipping($subtotal) : null,
            // Ödenecek nihai tutar: indirimli ara toplam + kargo
            'netTotal'      => $subtotal + $shippingCost,
            'coupon'        => $coupon,
            'couponDropped' => $couponDropped,
            'removed'       => $removed,
            'adjusted'      => $adjusted,
        ];
    }

    /**
     * Session'daki sepeti veritabanıyla senkronlar ve normalize edilmiş hâlini
     * geri yazar.
     *
     * @return array{cart: array, total: float, removed: array, adjusted: array}
     */
    private function syncCart(): array
    {
        $sessionCart = session()->get('cart', []);

        if (empty($sessionCart)) {
            return ['cart' => [], 'total' => 0.0, 'removed' => [], 'adjusted' => []];
        }

        $products = Product::whereIn('id', array_keys($sessionCart))->get()->keyBy('id');

        $cart     = [];
        $total    = 0.0;
        $removed  = [];
        $adjusted = [];

        foreach ($sessionCart as $productId => $line) {
            $product = $products->get($productId);

            // Ürün silinmiş veya yayından kaldırılmış
            if (!$product) {
                $removed[] = $line['name'] ?? "#{$productId}";
                continue;
            }

            $requested = max(1, (int) ($line['quantity'] ?? 1));
            $quantity  = min($requested, max(0, (int) $product->stock));

            if ($quantity < 1) {
                $removed[] = $product->name;
                continue;
            }

            if ($quantity !== $requested) {
                $adjusted[$product->name] = $quantity;
            }

            // Fiyat/ad/görsel daima veritabanından — session'daki kopyaya güvenilmez.
            $cart[$product->id] = [
                'name'       => $product->name,
                'quantity'   => $quantity,
                'price'      => (float) $product->price,
                'image_path' => $product->image_path,
                'image_url'  => $product->image_url,
                'slug'       => $product->slug,
            ];

            $total += $product->price * $quantity;
        }

        if ($cart !== $sessionCart) {
            session()->put('cart', $cart);
        }

        return ['cart' => $cart, 'total' => $total, 'removed' => $removed, 'adjusted' => $adjusted];
    }

    /**
     * Sepette kırpılan/düşen ürünler için kullanıcıya gösterilecek uyarı metni.
     */
    private function cartWarning(array $removed, array $adjusted): ?string
    {
        $parts = [];

        if ($removed) {
            $parts[] = 'Stokta kalmadığı için sepetinizden çıkarıldı: ' . implode(', ', $removed) . '.';
        }

        foreach ($adjusted as $name => $quantity) {
            $parts[] = "\"{$name}\" için stok yetersiz; adet {$quantity} olarak güncellendi.";
        }

        return $parts ? implode(' ', $parts) : null;
    }

    /**
     * Sepeti görüntüle.
     */
    public function index()
    {
        $totals = $this->calculateTotals();

        if ($warning = $this->cartWarning($totals['removed'], $totals['adjusted'])) {
            session()->flash('error', $warning);
        }

        return view('cart', $totals);
    }

    /**
     * Sepete ürün ekle.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'nullable|integer|min:1|max:' . self::MAX_QUANTITY_PER_ITEM,
        ], [
            'product_id.required' => 'Ürün seçilmedi.',
            'product_id.exists'   => 'Ürün bulunamadı.',
            'quantity.integer'    => 'Adet bir tam sayı olmalıdır.',
            'quantity.min'        => 'Adet en az 1 olmalıdır.',
            'quantity.max'        => 'Tek seferde en fazla ' . self::MAX_QUANTITY_PER_ITEM . ' adet ekleyebilirsiniz.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock <= 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu ürün şu anda stokta bulunmuyor.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Bu ürün şu anda stokta bulunmuyor.');
        }

        $cart    = session()->get('cart', []);
        $current = (int) ($cart[$product->id]['quantity'] ?? 0);
        $desired = $current + (int) ($validated['quantity'] ?? 1);

        // Stok ve tek üründen alınabilecek üst sınırla kırp
        $quantity = min($desired, $product->stock, self::MAX_QUANTITY_PER_ITEM);

        $cart[$product->id] = [
            'name'       => $product->name,
            'quantity'   => $quantity,
            'price'      => (float) $product->price,
            'image_path' => $product->image_path,
            'slug'       => $product->slug,
        ];

        session()->put('cart', $cart);

        $totalCount = array_sum(array_column($cart, 'quantity'));

        if ($quantity < $desired) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok yetersiz. \"{$product->name}\" için sepetinizdeki adet {$quantity} olarak ayarlandı.",
                    'cartCount' => $totalCount
                ]);
            }
            return redirect()->back()->with(
                'error',
                "Stok yetersiz. \"{$product->name}\" için sepetinizdeki adet {$quantity} olarak ayarlandı."
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ürün sepete başarıyla eklendi!',
                'cartCount' => $totalCount
            ]);
        }

        return redirect()->back()->with('success', 'Ürün sepete başarıyla eklendi!');
    }

    /**
     * Sepetteki ürün miktarını güncelle.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id'       => 'required|integer',
            'quantity' => 'required|integer|min:1|max:' . self::MAX_QUANTITY_PER_ITEM,
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$validated['id']])) {
            return response()->json([
                'success' => false,
                'message' => 'Ürün sepetinizde bulunamadı.',
            ], 404);
        }

        $product = Product::find($validated['id']);

        if (!$product || $product->stock <= 0) {
            unset($cart[$validated['id']]);
            session()->put('cart', $cart);

            return response()->json([
                'success' => false,
                'message' => 'Ürün artık satışta değil ve sepetinizden çıkarıldı.',
            ], 410);
        }

        $quantity = min($validated['quantity'], $product->stock);

        $cart[$product->id]['quantity'] = $quantity;
        session()->put('cart', $cart);

        return response()->json([
            'success'  => true,
            'quantity' => $quantity,
            'capped'   => $quantity < $validated['quantity'],
            'message'  => $quantity < $validated['quantity']
                ? "Stok yetersiz; adet {$quantity} olarak güncellendi."
                : null,
        ]);
    }

    /**
     * Sepetten ürün çıkar.
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$validated['id']])) {
            return redirect()->back()->with('error', 'Ürün sepetinizde bulunamadı.');
        }

        unset($cart[$validated['id']]);
        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Ürün sepetten çıkarıldı!');
    }
    /**
     * Ödeme formunu göster.
     */
    public function showCheckout()
    {
        $totals = $this->calculateTotals();

        // Sepet ödeme sayfasında değiştiyse kullanıcıyı sepete geri gönder;
        // beklemediği bir tutarla ödeme adımına girmesin.
        if ($warning = $this->cartWarning($totals['removed'], $totals['adjusted'])) {
            return redirect()->route('cart.index')->with('error', $warning);
        }

        if (empty($totals['cart'])) {
            return redirect()->route('cart.index')->with('error', 'Sepetiniz boş.');
        }

        $provinces = \App\Models\Province::orderBy('name', 'asc')->get();

        // Giriş yapmış kullanıcının kayıtlı adresleri; formu tek tıkla doldurmak için.
        $savedAddresses = auth()->check()
            ? auth()->user()->addresses()
                ->with(['province', 'district', 'neighborhood'])
                ->latest()
                ->get()
                ->map(fn ($a) => ['id' => $a->id, 'title' => $a->title] + $a->toCheckoutPayload())
                ->values()
            : collect();

        return view('checkout', array_merge($totals, compact('provinces', 'savedAddresses')));
    }

    /**
     * iyzico Checkout Form'u başlat.
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'required|email|max:255',
            'phone'           => 'required|string|max:20',
            'province_id'     => 'required|integer|exists:provinces,id',
            'district_id'     => 'required|integer|exists:districts,id',
            'neighborhood_id' => 'required|integer|exists:neighborhoods,id',
            'address_detail'  => 'required|string|max:500',
            'zip_code'        => 'nullable|string|max:20',
            'identity_number' => [
                'required',
                'string',
                'size:11',
                'regex:/^[0-9]{11}$/',
                function ($attribute, $value, $fail) {
                    if ($value[0] === '0') {
                        $fail('TC Kimlik No sıfır ile başlayamaz.');
                        return;
                    }
                    $digits = str_split($value);
                    $d = array_map('intval', $digits);
                    
                    $oddSum = $d[0] + $d[2] + $d[4] + $d[6] + $d[8];
                    $evenSum = $d[1] + $d[3] + $d[5] + $d[7];
                    
                    $tenthDigit = (($oddSum * 7) - $evenSum) % 10;
                    if ($tenthDigit < 0) {
                        $tenthDigit += 10;
                    }
                    if ($d[9] !== $tenthDigit) {
                        $fail('Geçersiz TC Kimlik No.');
                        return;
                    }
                    
                    $eleventhDigit = ($d[0] + $d[1] + $d[2] + $d[3] + $d[4] + $d[5] + $d[6] + $d[7] + $d[8] + $d[9]) % 10;
                    if ($eleventhDigit < 0) {
                        $eleventhDigit += 10;
                    }
                    if ($d[10] !== $eleventhDigit) {
                        $fail('Geçersiz TC Kimlik No.');
                    }
                }
            ],
            // Fatura adresi (farklıysa)
            'billing_province_id'     => 'required_unless:billing_same,1|nullable|integer|exists:provinces,id',
            'billing_district_id'     => 'required_unless:billing_same,1|nullable|integer|exists:districts,id',
            'billing_neighborhood_id' => 'required_unless:billing_same,1|nullable|integer|exists:neighborhoods,id',
            'billing_address_detail'  => 'required_unless:billing_same,1|nullable|string|max:500',
            'billing_zip'             => 'nullable|string|max:20',
            // Ticari fatura
            'company_name'    => 'nullable|string|max:255',
            'tax_number'      => 'nullable|string|max:10',
            'tax_office'      => 'nullable|string|max:100',
            // Onay kutuları
            'agree_sales'     => 'accepted',
            'agree_kvkk'      => 'accepted',
            'agree_accuracy'  => 'accepted',
        ], [
            'first_name.required'      => 'Ad alanı zorunludur.',
            'last_name.required'       => 'Soyad alanı zorunludur.',
            'email.required'           => 'E-posta alanı zorunludur.',
            'email.email'              => 'Geçerli bir e-posta adresi giriniz.',
            'phone.required'           => 'Telefon numarası zorunludur.',
            'province_id.required'     => 'İl seçimi zorunludur.',
            'district_id.required'     => 'İlçe seçimi zorunludur.',
            'neighborhood_id.required' => 'Mahalle seçimi zorunludur.',
            'address_detail.required'  => 'Adres detayı zorunludur.',
            'identity_number.required' => 'TC Kimlik No zorunludur.',
            'identity_number.size'     => 'TC Kimlik No 11 haneli olmalıdır.',
            'identity_number.regex'    => 'TC Kimlik No yalnızca rakamlardan oluşmalıdır.',
            'agree_sales.accepted'     => 'Mesafeli Satış Sözleşmesi\'ni onaylamanız gerekmektedir.',
            'agree_kvkk.accepted'      => 'KVKK Aydınlatma Metni\'ni onaylamanız gerekmektedir.',
            'agree_accuracy.accepted'  => 'Bilgilerin doğruluğundan sorumlu olduğunuzu onaylamanız gerekmektedir.',
        ]);

        $hadCoupon = (bool) session()->get('coupon');

        [
            'cart'          => $cart,
            'total'         => $total,
            'discount'      => $discount,
            'shippingCost'  => $shippingCost,
            'netTotal'      => $netTotal,
            'coupon'        => $coupon,
            'couponDropped' => $couponDropped,
            'removed'       => $removed,
            'adjusted'      => $adjusted,
        ] = $this->calculateTotals($request->email);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Sepetiniz boş.');
        }

        // Stok bu arada değiştiyse ödemeyi başlatma — kullanıcı gördüğü sepetten
        // farklı bir siparişe onay vermiş olmasın.
        if ($warning = $this->cartWarning($removed, $adjusted)) {
            return redirect()->route('cart.index')->with('error', $warning);
        }

        // Kupon bu adımda geçersizleştiyse (ör. kişi başı kullanım limiti ödeme
        // formundaki e-posta ile ancak burada tespit edilebiliyor) sessizce tam
        // fiyattan devam etmek yerine kullanıcıyı bilgilendir.
        if ($hadCoupon && $couponDropped) {
            return redirect()->route('checkout')->with(
                'error',
                'Kuponunuz artık geçerli değil (süresi dolmuş veya kullanım limitine ulaşılmış olabilir). Güncel tutarı kontrol edip tekrar deneyin.'
            );
        }

        if ($netTotal <= 0) {
            return redirect()->route('checkout')->with(
                'error',
                'İndirim sonrası ödenecek tutar sıfır veya altında kaldı. Lütfen bizimle iletişime geçin.'
            );
        }

        // Resolving İl, İlçe, Mahalle names to format city and address for backward compatibility
        $province = \App\Models\Province::findOrFail($request->province_id);
        $district = \App\Models\District::findOrFail($request->district_id);
        $neighborhood = \App\Models\Neighborhood::findOrFail($request->neighborhood_id);

        $cityText = $province->name;
        $addressText = $request->address_detail . ', ' . $neighborhood->name . ' Mah., ' . $district->name;

        if (!$request->boolean('billing_same') && $request->filled('billing_province_id') && $request->filled('billing_district_id') && $request->filled('billing_neighborhood_id')) {
            $billingProvince = \App\Models\Province::findOrFail($request->billing_province_id);
            $billingDistrict = \App\Models\District::findOrFail($request->billing_district_id);
            $billingNeighborhood = \App\Models\Neighborhood::findOrFail($request->billing_neighborhood_id);

            $billingCityText = $billingProvince->name;
            $billingAddressText = $request->billing_address_detail . ', ' . $billingNeighborhood->name . ' Mah., ' . $billingDistrict->name;
        } else {
            $billingCityText = $cityText;
            $billingAddressText = $addressText;
        }

        // Benzersiz conversation ID
        $conversationId = 'BW-' . strtoupper(Str::random(12));

        // Siparişi kaydet (pending)
        $order = Order::create([
            'user_id'                 => auth()->id(),
            'first_name'              => $request->first_name,
            'last_name'               => $request->last_name,
            'email'                   => $request->email,
            'phone'                   => $request->phone,
            'address'                 => $addressText,
            'city'                    => $cityText,
            'zip_code'                => $request->zip_code,
            'identity_number'         => $request->identity_number,
            'billing_address'         => $billingAddressText,
            'billing_city'            => $billingCityText,
            'is_corporate'            => $request->boolean('is_corporate'),
            'company_name'            => $request->company_name,
            'tax_number'              => $request->tax_number,
            'tax_office'              => $request->tax_office,
            'payment_method'          => 'iyzico Kredi Kartı',
            'shipping_method'         => $shippingCost > 0 ? 'Standart Kargo' : 'Ücretsiz Kargo',
            'shipping_cost'           => $shippingCost,
            'estimated_delivery_at'   => now()->addDays(3),
            'total_amount'            => $netTotal,
            'currency'                => 'TRY',
            'status'                  => OrderStatus::Pending->value,
            'iyzico_conversation_id'  => $conversationId,
            'cart_snapshot'           => $cart,
            'coupon_code'             => $coupon ? $coupon['code'] : null,
            'discount_amount'         => $discount,
            // Location ID fields
            'province_id'             => $request->province_id,
            'district_id'             => $request->district_id,
            'neighborhood_id'         => $request->neighborhood_id,
            'billing_province_id'     => !$request->boolean('billing_same') ? $request->billing_province_id : $request->province_id,
            'billing_district_id'     => !$request->boolean('billing_same') ? $request->billing_district_id : $request->district_id,
            'billing_neighborhood_id' => !$request->boolean('billing_same') ? $request->billing_neighborhood_id : $request->neighborhood_id,
        ]);

        // Sipariş kalemlerini kaydet
        foreach ($cart as $productId => $details) {
            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $productId,
                'product_name' => $details['name'],
                'quantity'     => $details['quantity'],
                'unit_price'   => $details['price'],
                'total_price'  => $details['price'] * $details['quantity'],
            ]);
        }

        // iyzico yapılandırması
        $options = new \Iyzipay\Options();
        $options->setApiKey(config('iyzico.api_key'));
        $options->setSecretKey(config('iyzico.secret_key'));
        $options->setBaseUrl(config('iyzico.base_url'));

        // Ödeme isteği oluştur
        $checkoutFormInitializeRequest = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
        $checkoutFormInitializeRequest->setLocale(\Iyzipay\Model\Locale::TR);
        $checkoutFormInitializeRequest->setConversationId($conversationId);
        // iyzico kuralı: price = sepet kalemlerinin toplamı (indirim/kargo hariç),
        // paidPrice = karttan gerçekten çekilecek tutar (indirim düşülmüş, kargo eklenmiş).
        $checkoutFormInitializeRequest->setPrice(number_format($total, 2, '.', ''));
        $checkoutFormInitializeRequest->setPaidPrice(number_format($netTotal, 2, '.', ''));
        $checkoutFormInitializeRequest->setCurrency(\Iyzipay\Model\Currency::TL);
        $checkoutFormInitializeRequest->setBasketId('BASKET-' . $order->id);
        $checkoutFormInitializeRequest->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $checkoutFormInitializeRequest->setCallbackUrl(route('payment.callback'));
        $checkoutFormInitializeRequest->setEnabledInstallments([1, 2, 3, 6, 9]);

        // Alıcı bilgileri
        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId('BUYER-' . $order->id);
        $buyer->setName($request->first_name);
        $buyer->setSurname($request->last_name);
        $buyer->setGsmNumber($request->phone);
        $buyer->setEmail($request->email);
        $buyer->setIdentityNumber($request->identity_number);
        $buyer->setRegistrationAddress($request->address);
        $buyer->setIp($request->ip());
        $buyer->setCity($request->city);
        $buyer->setCountry('Turkey');
        $buyer->setZipCode($request->zip_code ?? '34000');
        $checkoutFormInitializeRequest->setBuyer($buyer);

        // Teslimat adresi
        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($request->first_name . ' ' . $request->last_name);
        $shippingAddress->setCity($request->city);
        $shippingAddress->setCountry('Turkey');
        $shippingAddress->setAddress($request->address);
        $shippingAddress->setZipCode($request->zip_code ?? '34000');
        $checkoutFormInitializeRequest->setShippingAddress($shippingAddress);

        // Fatura adresi
        $checkoutFormInitializeRequest->setBillingAddress($shippingAddress);

        // Sepet kalemleri
        $basketItems = [];
        foreach ($cart as $productId => $details) {
            $item = new \Iyzipay\Model\BasketItem();
            $item->setId('ITEM-' . $productId);
            $item->setName(mb_substr($details['name'], 0, 100));
            $item->setCategory1('Genel');
            $item->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
            $item->setPrice(number_format($details['price'] * $details['quantity'], 2, '.', ''));
            $basketItems[] = $item;
        }
        $checkoutFormInitializeRequest->setBasketItems($basketItems);

        // iyzico'ya gönder
        $checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create(
            $checkoutFormInitializeRequest,
            $options
        );

        if ($checkoutFormInitialize->getStatus() !== 'success') {
            // Siparişi başarısız işaretle
            $order->update(['status' => OrderStatus::Failed->value]);
            return redirect()->route('checkout')
                ->with('error', 'Ödeme başlatılamadı: ' . $checkoutFormInitialize->getErrorMessage());
        }

        // iyzico token'ı kaydet
        $order->update(['iyzico_token' => $checkoutFormInitialize->getToken()]);

        // iyzico HTML form içeriğini view'e gönder
        $checkoutFormContent = $checkoutFormInitialize->getCheckoutFormContent();

        return view('payment.iyzico_form', compact('checkoutFormContent', 'order'));
    }

    /**
     * (Eski - artık kullanılmıyor, geriye dönük uyumluluk için bırakıldı)
     */
    public function checkout(Request $request)
    {
        return redirect()->route('checkout');
    }

    /**
     * Kupon uygula.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        $userId = auth()->id();
        $email  = auth()->user()?->email;
        if (!$coupon || $coupon->isExpiredOrLimitReached($userId, $email)) {
            return redirect()->back()->withInput($request->all())->with('error', 'Geçersiz, süresi geçmiş veya kullanım limiti dolmuş kupon kodu.');
        }

        session()->put('coupon', [
            'code'  => $coupon->code,
            'type'  => $coupon->type,
            'value' => $coupon->value,
        ]);

        return redirect()->back()->withInput($request->all())->with('success', 'Kupon başarıyla uygulandı!');
    }

    /**
     * Kuponu kaldır.
     */
    public function removeCoupon(Request $request)
    {
        session()->forget('coupon');
        return redirect()->back()->withInput($request->all())->with('success', 'Kupon kaldırıldı.');
    }
}
