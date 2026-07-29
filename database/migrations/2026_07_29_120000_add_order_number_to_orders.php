<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Müşteriye gösterilen sipariş numarası. Otomatik artan id sipariş
        // sayısını dışarıya sızdırıyor ve havale açıklamasında yanlış yazması
        // çok kolay. id birincil anahtar olarak yerinde kalır; bu alan
        // yalnızca görüntüleme ve müşteriyle iletişim içindir.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number', 32)->nullable()->unique()->after('id');
        });

        // Mevcut siparişlere de numara üretilir; aksi hâlde eski siparişler
        // panelde ve e-postalarda numarasız görünürdü.
        Order::whereNull('order_number')->orderBy('id')->chunkById(200, function ($orders) {
            foreach ($orders as $order) {
                $order->updateQuietly(['order_number' => Order::yeniSiparisNumarasi($order->created_at)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_number']);
            $table->dropColumn('order_number');
        });
    }
};
