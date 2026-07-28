<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Categories
        $categories = [
            // Electronics categories
            [
                'name' => 'Geliştirme Kartları',
                'slug' => 'gelistirme-kartlari',
                'channel' => 'electronics',
                'description' => 'Arduino, ESP32, ESP8266, Raspberry Pi ve IoT giyilebilir geliştirme kartları.',
                'image_path' => 'img/dfr1117/dfr1117-1.jpg'
            ],
            [
                'name' => 'LED Aydınlatma',
                'slug' => 'led-aydinlatma',
                'channel' => 'electronics',
                'description' => 'Bükülebilir S Tipi şerit LED\'ler, COB LED şeritler ve aydınlatma bileşenleri.',
                'image_path' => 'img/COBLED24v300k/2.jpg'
            ],
            [
                'name' => 'Sensör ve Modüller',
                'slug' => 'sensor-ve-moduller',
                'channel' => 'electronics',
                'description' => 'Mesafe, sıcaklık, nem, ışık, gaz ve hareket algılayıcı sensörler ve elektronik modüller.',
                'image_path' => 'img/espcamset/esp32camset1.jpg'
            ],
            [
                'name' => 'Güç Kaynakları ve Regülatörler',
                'slug' => 'guc-kaynaklari',
                'channel' => 'electronics',
                'description' => 'Voltaj düşürücü ve yükseltici regülatörler, pil şarj devreleri ve adaptörler.',
                'image_path' => 'img/esp32devkit30pin/1.jpg'
            ],
            [
                'name' => 'Lehimleme ve El Aletleri',
                'slug' => 'lehimleme-ve-el-aletleri',
                'channel' => 'electronics',
                'description' => 'Sıcaklık ayarlı kalem havyalar, lehim telleri, pastalar ve prototipleme araçları.',
                'image_path' => 'img/esp8266d1/1.jpg'
            ],
            [
                'name' => 'IoT ve Haberleşme Modülleri',
                'slug' => 'iot-haberlesme',
                'channel' => 'electronics',
                'description' => 'RFID modülleri, LoRa kartları ve kablosuz haberleşme alıcı-verici sistemleri.',
                'image_path' => 'img/esp32c61n8/esp32c61n8-1.jpg'
            ],
            // Health categories
            [
                'name' => 'Lens Aksesuarları',
                'slug' => 'lens-aksesuarlari',
                'channel' => 'health',
                'description' => 'Sızdırmaz kilitli lens saklama kutuları, temizlik ve bakım kapları.',
                'image_path' => 'img/Kutu Fotoları/26-07-22 08-06-47 9164.jpg'
            ],
            [
                'name' => 'DMV Ürünleri',
                'slug' => 'dmv-urunleri',
                'channel' => 'health',
                'description' => 'Orijinal ABD ithal DMV sert ve yumuşak kontakt lens takma, çıkarma vantuzları (Yetkili Satıcı).',
                'image_path' => 'img/Kutu Fotoları/26-07-22 08-07-00 9166.jpg'
            ]
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[$cat['slug']] = Category::create($cat);
        }

        // 2. Create Products
        $products = [
            // Geliştirme Kartları
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'DFRobot Beetle ESP32-C6 Mini Geliştirme Kartı (DFR1117)',
                'price' => 395.00,
                'discount_price' => 349.00,
                'stock' => 0,
                'rating' => 4.9,
                'description' => 'DFRobot Beetle serisinin ESP32-C6 tabanlı ultra kompakt üyesi. Yalnızca 25 × 20,5 mm boyutunda olmasına rağmen WiFi 6, Bluetooth 5 (LE), Zigbee ve Thread desteğini bir arada sunar. Kart üzerindeki lityum pil şarj devresi sayesinde harici şarj modülüne gerek kalmadan doğrudan LiPo pil bağlanabilir; bu özelliğiyle giyilebilir cihazlar, taşınabilir IoT sensörleri ve pille çalışan akıllı ev projeleri için özellikle uygundur.',
                'features' => [
                    'Marka' => 'DFRobot',
                    'Ürün Kodu' => 'DFR1117',
                    'Seri' => 'Beetle',
                    'Çip' => 'ESP32-C6, RISC-V 32-bit',
                    'İşlemci Hızı' => '160 MHz',
                    'Kablosuz' => 'WiFi 6 (802.11ax, 2.4 GHz) + Bluetooth 5 (LE) + IEEE 802.15.4 (Zigbee / Thread)',
                    'Pil Desteği' => 'Kart üstü lityum (LiPo) pil şarj devresi',
                    'Çalışma Gerilimi' => '3.3 V',
                    'Arayüzler' => 'I2C, SPI, UART',
                    'Çalışma Sıcaklığı' => '-10 °C ~ +60 °C',
                    'Boyut' => '25 × 20,5 mm (ultra kompakt)',
                    'Uyumluluk' => 'Arduino IDE, ESP-IDF, PlatformIO',
                    'Ürün Tipi' => 'Çoklu protokol geliştirme kartı'
                ],
                'image_path' => 'img/dfr1117/dfr1117-1.jpg',
                'meta_title' => 'DFRobot Beetle ESP32-C6 Mini Satın Al | DFR1117',
                'meta_description' => 'DFRobot Beetle ESP32-C6 Mini (DFR1117) giyilebilir geliştirme kartı en uygun fiyatla sitemizde. Dahili pil şarj devresi, WiFi 6, Zigbee ve Bluetooth 5 desteği.'
            ],
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'ESP32-C6-DevKitC-1-N8 WiFi 6 + Bluetooth 5 Geliştirme Kartı',
                'price' => 450.00,
                'discount_price' => 399.00,
                'stock' => 120,
                'rating' => 4.8,
                'description' => 'ESP32-C6-WROOM-1 modülü tabanlı, çok protokollü genel amaçlı geliştirme kartı. WiFi 6 (802.11ax), Bluetooth 5 (LE), Zigbee 3.0 ve Thread desteğini tek kart üzerinde sunar; Matter uyumlu akıllı ev ve IoT projeleri için idealdir. Tüm ESP32-C6 pinleri kartın iki yanındaki başlıklara çıkarılmıştır. Üzerindeki adreslenebilir RGB LED ve iki adet USB Type-C portu (biri native USB, biri USB-UART köprüsü) ile geliştirme ve hata ayıklama pratiktir.',
                'features' => [
                    'Model' => 'ESP32-C6-DevKitC-1-N8',
                    'Modül' => 'ESP32-C6-WROOM-1 (N8)',
                    'Çip' => 'ESP32-C6, RISC-V 32-bit tek çekirdek + düşük güç LP çekirdek',
                    'İşlemci Hızı' => '160 MHz / 20 MHz',
                    'Flash' => '8 MB (dahili SPI flash)',
                    'SRAM' => '512 KB HP SRAM + 16 KB LP SRAM',
                    'Kablosuz' => 'WiFi 6 (802.11ax) + Bluetooth 5 + IEEE 802.15.4 (Zigbee 3.0 / Thread)',
                    'Matter' => 'Matter protokolü desteği',
                    'USB' => '2x Type-C (USB-to-UART + native USB JTAG)',
                    'LED' => 'Adreslenebilir RGB LED',
                    'Çalışma Gerilimi' => '3.3 V',
                    'Üretici' => 'Espressif Systems'
                ],
                'image_path' => 'img/esp32c61n8/esp32c61n8-1.jpg',
                'meta_title' => 'ESP32-C6-DevKitC-1-N8 Geliştirme Kartı Satın Al',
                'meta_description' => 'Matter ve Zigbee uyumlu ESP32-C6-DevKitC-1-N8 geliştirme kartı stoklarımızda. Çift USB-C girişi ve WiFi 6 desteği ile yeni nesil IoT projelerine başlayın.'
            ],
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'ESP32-CAM Geliştirme Seti (OV2640 Kamera + MB Programlayıcı)',
                'price' => 1110.00,
                'discount_price' => 990.00,
                'stock' => 0,
                'rating' => 4.7,
                'description' => 'WiFi + Bluetooth destekli, kameralı IoT projeleri için eksiksiz geliştirme seti. ESP32-CAM ana kartı, OV2640 kamera modülü and ESP32-CAM-MB programlama kartı bir arada gelir. Programlama kartı sayesinde ekstra FTDI adaptör gerekmeden doğrudan Type-C kablosuyla bilgisayardan kod yüklenebilir.',
                'features' => [
                    'Ana Kart' => 'ESP32-CAM (ESP-32S modül)',
                    'Çip' => 'ESP32-D0WD-V3, çift çekirdek Xtensa LX6',
                    'İşlemci Hızı' => '240 MHz',
                    'Kamera' => 'OV2640, 2 Megapiksel',
                    'Flash' => '4 MB',
                    'PSRAM' => 'Dahili (Video akışı için)',
                    'Kablosuz' => 'WiFi 802.11 b/g/n + Bluetooth 4.2 / BLE',
                    'Programlama Kartı' => 'ESP32-CAM-MB, Type-C girişli',
                    'microSD Desteği' => 'Kart üstü microSD yuvası'
                ],
                'image_path' => 'img/espcamset/esp32camset1.jpg',
                'meta_title' => 'ESP32-CAM Kameralı Geliştirme Seti Satın Al',
                'meta_description' => 'FTDI gerektirmeyen Type-C girişli ESP32-CAM-MB programlama kartı ve OV2640 2MP kamera içeren komple ESP32-CAM seti uygun fiyatla kapınızda.'
            ],
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'ESP32-C3 SuperMini Type-C WiFi + Bluetooth Geliştirme Kartı',
                'price' => 379.00,
                'discount_price' => 339.00,
                'stock' => 200,
                'rating' => 4.6,
                'description' => 'RISC-V çekirdekli, ultra kompakt ESP32-C3 geliştirme kartı. WiFi ve Bluetooth 5.0 (LE) desteğini son derece küçük bir pcb üzerinde sunar; giyilebilir cihazlar, IoT düğümleri ve dar alanlı projeler için mükemmeldir. Ekstra USB-seri çip gerektirmeden yerleşik native USB ile doğrudan Type-C üzerinden programlanır.',
                'features' => [
                    'Model' => 'ESP32-C3 SuperMini',
                    'Çip' => 'ESP32-C3 (QFN32, rev v0.4), RISC-V tek çekirdek',
                    'İşlemci Hızı' => '160 MHz',
                    'USB Arayüzü' => 'Native USB (USB-Serial/JTAG)',
                    'USB Portu' => 'Type-C',
                    'Flash' => '4 MB (dahili)',
                    'Kablosuz' => 'WiFi 802.11 b/g/n (2.4 GHz) + Bluetooth 5.0 (LE)',
                    'Anten' => 'PCB anten',
                    'LED' => 'Kart üstü RGB LED',
                    'Boyutlar' => '22.5 × 18 mm (ultra küçük)'
                ],
                'image_path' => 'img/esp32 c3 süper min/esp32c3mini3.jpg',
                'meta_title' => 'ESP32-C3 SuperMini WiFi Bluetooth Kartı',
                'meta_description' => 'Avuç içi boyuttaki ESP32-C3 SuperMini geliştirme kartı Type-C bağlantısı ve RISC-V işlemcisiyle robotik ve giyilebilir cihaz projelerinize güç katsın.'
            ],
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'ESP32 D1 Mini Type-C WiFi + Bluetooth Geliştirme Kartı',
                'price' => 690.00,
                'discount_price' => 599.00,
                'stock' => 95,
                'rating' => 4.7,
                'description' => 'WeMos/LOLIN D1 Mini formatında, çift çekirdekli ESP32 tabanlı geliştirme kartı. WiFi ve Bluetooth\'u bir arada sunar; akıllı ev, IoT, sensör ve otomasyon projeleri için idealdir. Type-C girişli ve CP2102 kaliteli USB-seri çipine sahip olduğundan sürücü sorunu yaşanmadan hızlıca kod yüklenebilir.',
                'features' => [
                    'Model' => 'ESP32 D1 Mini (WeMos formatı)',
                    'Çip' => 'ESP32-D0WD-V3, çift çekirdek Xtensa LX6 + LP Core',
                    'İşlemci Hızı' => '240 MHz',
                    'USB-Seri Çip' => 'CP2102 (Silicon Labs)',
                    'USB Portu' => 'Type-C',
                    'Flash' => '4 MB',
                    'Kablosuz' => 'WiFi + Bluetooth 4.2 / BLE',
                    'Çalışma Gerilimi' => '3.3 V',
                    'Sertifikalar' => 'CE, RoHS'
                ],
                'image_path' => 'img/esp32d1/ok.jpeg',
                'meta_title' => 'ESP32 D1 Mini Type-C Geliştirme Kartı Satın Al',
                'meta_description' => 'WeMos D1 Mini boyutunda CP2102 çipli ESP32 D1 Mini geliştirme kartı. Type-C portu, WiFi ve Bluetooth desteğiyle uygun fiyata sipariş verin.'
            ],
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'ESP8266 D1 Mini Type-C Geliştirme Kartı (FTDI Çipli)',
                'price' => 434.00,
                'discount_price' => 389.00,
                'stock' => 140,
                'rating' => 4.6,
                'description' => 'WeMos/LOLIN uyumlu ESP-12F modüllü ESP8266 D1 Mini geliştirme kartı. Ucuz CH340 klon kartlara kıyasla orijinal FTDI (FT232) USB-seri dönüştürücü çipine sahiptir. Bu sayede Mac, Windows ve Linux sistemlerde sürücü kurma problemi yaşanmaz, kararlı veri aktarımı sunar. Type-C bağlantılıdır.',
                'features' => [
                    'Model' => 'D1 Mini (ESP8266)',
                    'Modül' => 'ESP-12F',
                    'Çip' => 'ESP8266EX',
                    'İşlemci Hızı' => '80/160 MHz',
                    'USB-Seri Çip' => 'FTDI (FT232) - Sürücü sorunsuz',
                    'USB Portu' => 'Type-C',
                    'Flash' => '4 MB',
                    'Kablosuz' => 'WiFi 802.11 b/g/n',
                    'GPIO' => '11x dijital, 1x analog (A0), I2C, SPI'
                ],
                'image_path' => 'img/esp8266d1/1.jpg',
                'meta_title' => 'ESP8266 D1 Mini FTDI Çipli Type-C Satın Al',
                'meta_description' => 'Orijinal FTDI çipli ESP8266 D1 Mini Type-C geliştirme kartı. Sürücü kurulum derdi olmadan bilgisayarınızda kolayca kodlamaya başlayın.'
            ],
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'ESP32 DevKit 30 Pin Type-C Geliştirme Kartı (V3 Güncel Çip)',
                'price' => 676.00,
                'discount_price' => 595.00,
                'stock' => 180,
                'rating' => 4.8,
                'description' => 'ESP-WROOM-32 modüllü standart ESP32 DevKit 30 Pin geliştirme kartı. Kart üzerinde en güncel silikon revizyonu olan ESP32-D0WD-V3 çipi ve stabil CP2102 USB-seri dönüştürücüsü barındırır. Type-C girişi sayesinde daha modern ve dayanıklı bir bağlantı tecrübesi yaşatır.',
                'features' => [
                    'Model' => 'ESP32 DevKit 30 Pin',
                    'Modül' => 'ESP-WROOM-32',
                    'Çip' => 'ESP32-D0WD-V3 (En güncel v3.1 revizyonu)',
                    'İşlemci Hızı' => '240 MHz çift çekirdek',
                    'USB-Seri Çip' => 'CP2102 (Silicon Labs)',
                    'USB Portu' => 'Type-C',
                    'Flash' => '4 MB',
                    'Kablosuz' => 'WiFi + Bluetooth 4.2 / BLE',
                    'Pin Sayısı' => '30 Pin'
                ],
                'image_path' => 'img/esp32devkit30pin/1.jpg',
                'meta_title' => 'ESP32 DevKit 30 Pin CP2102 Type-C Geliştirme Kartı',
                'meta_description' => 'En güncel V3.1 silikonlu ESP-WROOM-32 modüllü ESP32 DevKit 30 pin kartı. Type-C girişi ve CP2102 çipiyle yüksek kararlılıkta IoT projeleri tasarlayın.'
            ],
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'ESP32-S3 Super Mini Type-C Geliştirme Kartı',
                'price' => 579.00,
                'discount_price' => 499.00,
                'stock' => 60,
                'rating' => 4.9,
                'description' => 'Başparmak boyutunda olmasına rağmen içinde çift çekirdekli güçlü ESP32-S3 işlemcisi barındıran üstün bir geliştirme kartı. USB-C girişi sayesinde ek programlayıcıya ihtiyaç duymadan doğrudan bilgisayara bağlanıp kod yüklenebilir. 4MB flash ve 2MB PSRAM kapasitesi ile yapay zeka ve ses işleme projelerine uygundur.',
                'features' => [
                    'Model' => 'ESP32-S3 Super Mini',
                    'Çip' => 'ESP32-S3 QFN56, rev v0.2',
                    'İşlemci' => 'Çift çekirdek Xtensa LX7 + LP çekirdek (240 MHz)',
                    'Flash' => '4 MB (XMC)',
                    'PSRAM' => '2 MB',
                    'USB' => 'USB-Serial/JTAG (yerleşik native)',
                    'Paket İçeriği' => '1x ESP32-S3 Kart + 2x 9\'lu lehimlenmemiş pin header'
                ],
                'image_path' => 'img/S3 super mini/s3supermini.jpg',
                'meta_title' => 'ESP32-S3 Super Mini 4MB Flash 2MB PSRAM',
                'meta_description' => 'Güçlü çift çekirdekli ESP32-S3 Super Mini geliştirme kartı Type-C portu, 4MB flash ve 2MB PSRAM bellek kapasitesiyle en uygun fiyata satışta.'
            ],
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'Mini560 5V Voltaj Düşürücü Regülatör Modülü',
                'price' => 278.00,
                'discount_price' => 249.00,
                'stock' => 90,
                'rating' => 4.7,
                'description' => 'Yüksek verimli ve ultra küçük boyutlu Mini560 5V DC-DC voltaj düşürücü (buck) regülatör modülü. Robotik projeler, mikrodenetleyici beslemeleri ve araç içi elektronikler için mükemmel bir gerilim dönüştürme performansı sunar.',
                'features' => [
                    'Model' => 'Mini-560 5V',
                    'Giriş Gerilimi' => '6V - 20V DC',
                    'Çıkış Gerilimi' => '5V DC',
                    'Çıkış Akımı' => 'Kararlı 5A (Maksimum 6A)',
                    'Dönüşüm Verimliliği' => '%95-97 (Yüksek Verim)',
                    'Çalışma Frekansı' => '500 kHz',
                    'Koruma' => 'Aşırı sıcaklık ve kısa devre koruması'
                ],
                'image_path' => '', // Fallback to Chip SVG
                'meta_title' => 'Mini560 5V Voltaj Düşürücü Regülatör Modülü Satın Al',
                'meta_description' => 'Yüksek verimli Mini560 5V DC-DC Buck Regülatör modülü uygun fiyat ve hızlı kargo ile sitemizde. 5A kararlı çıkış gücü.'
            ],
            [
                'category_slug' => 'gelistirme-kartlari',
                'name' => 'TP4056 USB-C Lityum Pil Şarj Modülü (Korumalı)',
                'price' => 198.00,
                'discount_price' => 169.00,
                'stock' => 150,
                'rating' => 4.8,
                'description' => 'Lityum pillerinizi (örneğin 18650 piller) güvenli bir şekilde şarj etmek için tasarlanmış USB-C girişli TP4056 şarj devresi modülü. Üzerinde yerleşik olarak bulunan pil koruma entegresi (DW01) sayesinde pili aşırı şarj, aşırı deşarj ve aşırı akıma karşı korur.',
                'features' => [
                    'Entegre' => 'TP4056 + DW01 Pil Koruma',
                    'Şarj Arayüzü' => 'USB Type-C',
                    'Şarj Akımı' => '1A (Ayarlanabilir)',
                    'Giriş Voltajı' => '4.5V - 5.5V DC',
                    'Şarj Kesim Voltajı' => '4.2V ±%1',
                    'Deşarj Koruma Sınırı' => '2.5V',
                    'LED Göstergeler' => 'Kırmızı (Şarj oluyor) / Mavi-Yeşil (Şarj tamamlandı)'
                ],
                'image_path' => '', // Fallback to Chip SVG
                'meta_title' => 'TP4056 USB-C Korumalı Lityum Şarj Modülü',
                'meta_description' => 'USB-C tipi TP4056 korumalı lityum pil şarj devresi en ucuz fiyatla kapınızda. Aşırı deşarj ve akım korumalı DW01 entegreli lityum şarj kartı.'
            ],

            // LED Aydınlatma
            [
                'category_slug' => 'led-aydinlatma',
                'name' => '24V 2835 S Tipi Bükülebilir Şerit LED (5 Metre)',
                'price' => 175.00,
                'discount_price' => 149.00,
                'stock' => 100,
                'rating' => 4.5,
                'description' => 'SMD 2835 yüksek verimli LED çiplerine sahip bu şerit LED, metrede 120 adet LED ile homojen bir aydınlatma sunar. Özel kesimli S şeklinde tasarlanmış PCB yapısı sayesinde şerit yatay düzlemde de bükülebilir; köşe dönüşlerinde lehimleme veya ek konnektör kullanmadan kolayca uygulanabilir.',
                'features' => [
                    'Çalışma Gerilimi' => '24V DC',
                    'Güç Tüketimi' => '12W / metre (5 Metre Toplam 60W)',
                    'LED Yoğunluğu' => '120 adet / metre',
                    'LED Tipi' => 'SMD 2835',
                    'Renk Sıcaklığı' => '3000K Gün Işığı (Sarı)',
                    'PCB Yapısı' => '8 mm genişlik, S Tipi Bükülebilir',
                    'Koruma Sınıfı' => 'IP20 (İç Mekan kullanıma uygun)',
                    'Montaj' => 'Arka yüzey 3M yapışkan bantlı',
                    'Uzunluk' => '5 Metre Rulo'
                ],
                'image_path' => 'img/2835300k 120led/2835300k4ok.jpg',
                'meta_title' => '24V 2835 Bükülebilir S Tipi Şerit LED Gün Işığı',
                'meta_description' => 'Köşelerde lehim yapmaya son veren S tipi bükülebilir 24V 2835 şerit LED 5 metrelik rulo halinde en ucuz fiyatlarla sitemizde. 3000K Gün Işığı tonundadır.'
            ],
            [
                'category_slug' => 'led-aydinlatma',
                'name' => 'Wise Solutions 24V COB Şerit LED - 5 Metre (3000K Gün Işığı)',
                'price' => 260.00,
                'discount_price' => 225.00,
                'stock' => 80,
                'rating' => 4.8,
                'description' => 'Wise Solutions markalı 24V COB Şerit LED, noktacıklı LED görüntüsünü tamamen ortadan kaldıran yeni nesil kesintisiz aydınlatma teknolojisine sahiptir. Metrede 528 adet yoğun LED barındırarak gözü yormayan çizgisel, kesintisiz gün ışığı aydınlatması sağlar. Mutfak, asma tavan ve dolap aydınlatmaları için idealdir.',
                'features' => [
                    'Marka' => 'Wise Solutions',
                    'LED Tipi' => 'COB (Kesintisiz Işık Çizgisi)',
                    'Çalışma Gerilimi' => '24V DC',
                    'Güç Tüketimi' => '13W / metre',
                    'LED Sayısı' => '528 adet / metre',
                    'Renk Sıcaklığı' => '3000K Gün Işığı (Sarı)',
                    'Koruma Sınıfı' => 'IP20 (İç Mekan)',
                    'PCB Genişliği' => '8 mm',
                    'Uzunluk' => '5 Metre'
                ],
                'image_path' => 'img/COBLED24v300k/2.jpg',
                'meta_title' => 'Wise Solutions 24V COB LED Şerit 3000K Gün Işığı',
                'meta_description' => 'Kesintisiz noktasız ışık sunan Wise Solutions COB şerit LED 24V gerilim ve metrede 528 LED yoğunluğu ile üst düzey aydınlatma sağlar. 5m rulo halinde satın al.'
            ],


            // Lens Aksesuarları (non-DMV Ürünleri)
            [
                'category_slug' => 'lens-aksesuarlari',
                'name' => 'Lüks Aynalı Skleral Lens Saklama Kutusu (Vantuzlu Set)',
                'price' => 160.00,
                'discount_price' => 139.00,
                'stock' => 85,
                'rating' => 4.8,
                'description' => 'Skleral ve sert gaz geçirgen (RGP) kontakt lens kullanıcıları için tasarlanmış, aynalı kilitli koruma kutusu. Paket içeriğinde özel mini vantuz aparatı ve yerleştirme cımbızı ile birlikte komple settir.',
                'features' => [
                    'Marka' => 'Wise Solutions',
                    'Ürün Tipi' => 'Lens Saklama Kutusu Seti',
                    'Malzeme' => 'BPA içermeyen sert plastik',
                    'Ayna' => 'Dahili ayna mevcut',
                    'Renk' => 'Gümüş Gri',
                    'Uyumluluk' => 'Skleral ve RGP sert lensler'
                ],
                'image_path' => 'img/Kutu Fotoları/26-07-22 08-06-47 9164.jpg',
                'meta_title' => 'Lüks Aynalı Skleral Lens Saklama Kutusu Seti',
                'meta_description' => 'Skleral ve sert lenslerinizi güvenle saklayabileceğiniz aynalı ve kilitli lüks saklama kutusu seti en uygun fiyatlarla sitemizde.'
            ],
            [
                'category_slug' => 'lens-aksesuarlari',
                'name' => 'Sızdırmaz Kilitli RGP Sert Lens Kabı',
                'price' => 110.00,
                'discount_price' => 95.00,
                'stock' => 0,
                'rating' => 4.7,
                'description' => 'Seyahatlerinizde lens solüsyonunuzun dışarıya sızmasını %100 önleyen kilitli kapak mekanizmalı sert lens koruma kabı. Çift hazneli steril kullanım sunar.',
                'features' => [
                    'Marka' => 'Wise Solutions',
                    'Ürün Tipi' => 'Sızdırmaz Lens Kabı',
                    'Kilit' => 'Kilitli sızdırmaz conta',
                    'Hazne' => 'Çift hazneli (Sağ/Sol ayrı)',
                    'Renk' => 'Mavi-Beyaz'
                ],
                'image_path' => 'img/Kutu Fotoları/26-07-22 08-06-49 9165.jpg',
                'meta_title' => 'Sızdırmaz Kilitli RGP Sert Lens Kabı',
                'meta_description' => 'Solüsyon sızdırmayan kilit contalı RGP sert kontakt lens kabı en uygun fiyatla kapınızda. Taşınabilir seyahat dostu tasarım.'
            ],
            [
                'category_slug' => 'lens-aksesuarlari',
                'name' => 'Aynalı Yuvarlak Kontakt Lens Kutusu (Pudra Pembe)',
                'price' => 125.00,
                'stock' => 60,
                'rating' => 4.6,
                'description' => 'Şık yuvarlak dış tasarımı, dahili makyaj aynası ve hijyenik cımbız bölmesi ile çantanızda güvenle taşıyabileceğiniz lüks lens kutusu.',
                'features' => [
                    'Marka' => 'Wise Solutions',
                    'Ürün Tipi' => 'Aynalı Kontak Lens Kutusu',
                    'Dış Tasarım' => 'Yuvarlak, Pudra Pembe',
                    'Aksesuarlar' => 'Cımbız ve yerleştirici dahil'
                ],
                'image_path' => 'img/Kutu Fotoları/26-07-22 08-07-19 9168.jpg',
                'meta_title' => 'Aynalı Yuvarlak Kontakt Lens Kutusu (Pudra Pembe)',
                'meta_description' => 'Dahili aynası ve taşıma cımbızıyla şık pudra pembesi yuvarlak kontakt lens saklama kutusu Wise Solutions güvencesiyle.'
            ],
            [
                'category_slug' => 'lens-aksesuarlari',
                'name' => 'Çiftli Hijyenik Lens Saklama Kutusu (Aynalı Klasik Set)',
                'price' => 140.00,
                'stock' => 0,
                'rating' => 4.5,
                'description' => 'Sağ ve sol göz lenslerinizi ayrı bölmelerde güvenle saklamanızı sağlayan, aynalı ve kilitli şık saklama kutusu.',
                'features' => [
                    'Marka' => 'Wise Solutions',
                    'Ürün Tipi' => 'Çiftli Saklama Kutusu',
                    'Tasarım' => 'Dikdörtgen Klasik Set',
                    'Renk' => 'Mat Siyah'
                ],
                'image_path' => 'img/Kutu Fotoları/26-07-22 08-07-23 9169.jpg',
                'meta_title' => 'Çiftli Hijyenik Lens Saklama Kutusu',
                'meta_description' => 'Sağ ve sol göz lensleri için ayrı hazneli, aynalı klasik dikdörtgen saklama kutusu seti en ucuz fiyatlarla sitemizde.'
            ],

            // Sensör ve Modüller
            [
                'category_slug' => 'sensor-ve-moduller',
                'name' => 'HC-SR04 Ultrasonik Mesafe Sensörü',
                'price' => 65.00,
                'stock' => 0,
                'rating' => 4.6,
                'description' => '2cm ile 400cm arasındaki engelleri hassas bir şekilde algılayabilen, Arduino ve ESP32 projelerinde mesafe ölçümü ve robotik uygulamalarda engel aşma amacıyla sıklıkla kullanılan popüler ultrasonik sensör modülü.',
                'features' => [
                    'Model' => 'HC-SR04',
                    'Çalışma Gerilimi' => '5V DC',
                    'Mesafe Sınırı' => '2cm - 400cm',
                    'Açı' => '15 derece algılama açısı',
                    'Çalışma Frekansı' => '40 kHz'
                ],
                'image_path' => '', // Fallback to custom SVG
                'meta_title' => 'HC-SR04 Ultrasonik Mesafe Sensörü Satın Al',
                'meta_description' => 'HC-SR04 ultrasonik mesafe sensörü en ucuz fiyatla sitemizde. Arduino projeleriniz için engel algılama ve mesafe ölçüm sensörü.'
            ],
            [
                'category_slug' => 'sensor-ve-moduller',
                'name' => 'DHT11 Isı ve Nem Sensör Kartı',
                'price' => 85.00,
                'stock' => 0,
                'rating' => 4.5,
                'description' => 'Ortamdaki sıcaklık ve bağıl nem oranını dijital sinyal çıkışı olarak veren, kalibrasyon gerektirmeyen tek kablolu (one-wire) sensör modülü. Akıllı ev ve meteoroloji projeleri için uygundur.',
                'features' => [
                    'Model' => 'DHT11',
                    'Çalışma Gerilimi' => '3V - 5.5V DC',
                    'Nem Ölçüm Aralığı' => '%20 - %90 RH (±%5 Hata)',
                    'Sıcaklık Ölçüm Aralığı' => '0°C - 50°C (±2°C Hata)'
                ],
                'image_path' => '', // Fallback to custom SVG
                'meta_title' => 'DHT11 Sıcaklık ve Nem Sensörü Satın Al',
                'meta_description' => 'Dijital DHT11 ısı ve nem ölçer sensör kartı en uygun fiyatlarla kapınızda. Ev otomasyon projelerinize entegre edin.'
            ],

            // Güç Kaynakları ve Regülatörler
            [
                'category_slug' => 'guc-kaynaklari',
                'name' => 'LM2596 DC-DC Ayarlanabilir Voltaj Düşürücü Regülatör',
                'price' => 95.00,
                'stock' => 0,
                'rating' => 4.7,
                'description' => 'Giriş voltajını üzerindeki hassas trimpot yardımıyla düşüren ve çıkış voltajını sabitleyen yüksek verimli voltaj regülatörü kartı. Robotik projeler ve harici devre beslemeleri için vazgeçilmezdir.',
                'features' => [
                    'Model' => 'LM2596',
                    'Giriş Voltajı' => '3.2V - 40V DC',
                    'Çıkış Voltajı' => '1.25V - 35V DC (Ayarlanabilir)',
                    'Akım Çıkışı' => 'Maksimum 3A (Soğutuculu)'
                ],
                'image_path' => '', // Fallback to custom SVG
                'meta_title' => 'LM2596 DC-DC Voltaj Düşürücü Satın Al',
                'meta_description' => 'Ayarlanabilir LM2596 buck dönüştürücü voltaj regülatör modülü. Uygun fiyatlı DC-DC voltaj düşürücü kartı.'
            ],

            // Lehimleme ve El Aletleri
            [
                'category_slug' => 'lehimleme-ve-el-aletleri',
                'name' => '60W Isı Ayarlı Kalem Havya',
                'price' => 349.00,
                'stock' => 0,
                'rating' => 4.8,
                'description' => 'Üzerindeki sıcaklık ayar potansı sayesinde 200°C ile 450°C arasında hassas çalışma imkanı sunan, rezistansı dayanıklı profesyonel kalem havya.',
                'features' => [
                    'Güç' => '60W',
                    'Sıcaklık' => '200°C - 450°C (Ayarlanabilir)',
                    'Çalışma Voltajı' => '220V AC',
                    'Kordon Boyu' => '1.4 Metre'
                ],
                'image_path' => '', // Fallback to custom SVG
                'meta_title' => '60W Isı Ayarlı Kalem Havya Satın Al',
                'meta_description' => 'Rezistanslı 60W sıcaklık ayarlı kalem havya en ucuz fiyatlarla sitemizde. Lehimleme işleriniz için ideal el aleti.'
            ],

            // IoT ve Haberleşme Modülleri
            [
                'category_slug' => 'iot-haberlesme',
                'name' => 'RC522 RFID NFC Kart Okuyucu Modül (13.56 MHz)',
                'price' => 110.00,
                'stock' => 0,
                'rating' => 4.8,
                'description' => 'MFRC522 çipi tabanlı, 13.56 MHz frekansında çalışan ve kart/anahtarlık okuma/yazma işlemlerini SPI arayüzü üzerinden hızlıca gerçekleştiren popüler RFID kiti.',
                'features' => [
                    'Model' => 'RC522',
                    'Çalışma Frekansı' => '13.56 MHz',
                    'Haberleşme Arayüzü' => 'SPI',
                    'Paket İçeriği' => 'RC522 Kart, RFID Anahtarlık, RFID Kart, Düz ve 90° pin header'
                ],
                'image_path' => '', // Fallback to custom SVG
                'meta_title' => 'RC522 RFID Kart Okuyucu Modülü Satın Al',
                'meta_description' => 'Arduino uyumlu RC522 RFID okuyucu seti anahtarlık ve kartı ile komple paket halinde en uygun fiyata satışta.'
            ],

            // ─── DMV Ürünleri (9 ürün) ───
            [
                'category_slug' => 'dmv-urunleri',
                'name' => 'DMV Ultra Kontakt Lens Takma ve Çıkarma Aparatı',
                'price' => 245.00,
                'discount_price' => 219.00,
                'stock' => 0,
                'rating' => 4.9,
                'description' => 'DMV Ultra, 4 farklı renk seçeneğiyle sunulan DMV Corporation\'ın amiral gemisi modelidir. Sert (RGP) ve yumuşak kontakt lensleri göze temas etmeden güvenle takıp çıkarmak için tasarlanmış premium tıbbi vantuz. Ergonomik kavrama yüzeyi ve yumuşak ucu sayesinde her kullanımda tam kontrol sağlar. ABD orijinal imalatıdır.',
                'features' => [
                    'Marka' => 'DMV Corporation',
                    'Menşei' => 'ABD (Orijinal İthal)',
                    'Renk Seçenekleri' => '4 farklı renk',
                    'Ürün Tipi' => 'Sert ve yumuşak lens takma-çıkarma aparatı',
                    'Malzeme' => 'Yumuşak tıbbi silikon + ergonomik sap',
                    'Garanti' => 'Yetkili Satıcı Orijinallik Garantili'
                ],
                'image_path' => 'img/dmv plungerler/dmvultra.jpeg',
                'meta_title' => 'DMV Ultra Kontakt Lens Aparatı — 4 Renk | Yetkili Satıcı',
                'meta_description' => 'Orijinal ABD üretimi DMV Ultra kontakt lens takma çıkarma aparatı. 4 renk seçeneği, premium tıbbi silikon. DMV Türkiye yetkili satıcısı.'
            ],
            [
                'category_slug' => 'dmv-urunleri',
                'name' => 'DMV Scleral Cup Skleral Lens Vantuzu',
                'price' => 220.00,
                'stock' => 0,
                'rating' => 4.9,
                'description' => 'DMV Scleral Cup, skleral ve büyük çaplı kontakt lens kullanıcıları için özel olarak üretilmiş geniş ağızlı vantuzlu aparattır. 4 farklı renk seçeneğiyle sunulan bu model, narin skleral lenslerinizi hasarsız, güvenli ve hijyenik şekilde takıp çıkarmanızı sağlar.',
                'features' => [
                    'Marka' => 'DMV Corporation',
                    'Menşei' => 'ABD (Orijinal İthal)',
                    'Renk Seçenekleri' => '4 farklı renk',
                    'Ürün Tipi' => 'Skleral lens takma-çıkarma aparatı',
                    'Malzeme' => 'Yumuşak tıbbi kauçuk',
                    'Uyumluluk' => 'Skleral ve büyük çaplı lensler, protez gözler'
                ],
                'image_path' => 'img/dmv plungerler/Scleral-Cups-main.jpg',
                'meta_title' => 'DMV Scleral Cup Skleral Lens Aparatı — 4 Renk',
                'meta_description' => 'DMV Scleral Cup geniş çaplı skleral lens aparatı. 4 renk seçeneği. Protez göz ve skleral lens takıp çıkarma için en güvenli çözüm.'
            ],
            [
                'category_slug' => 'dmv-urunleri',
                'name' => 'DMV Vented Scleral Cup Havalandırmalı Skleral Vantuz',
                'price' => 235.00,
                'stock' => 0,
                'rating' => 4.8,
                'description' => 'Havalandırmalı (vented) vantuz tasarımı sayesinde lensin gözden ayrılmasını kolaylaştıran DMV Vented Scleral Cup, skleral lens kullanıcılarının en çok tercih ettiği modeldir. Kontrollü hava kanalı sayesinde hassas lenslerinize hasar vermeden çıkarırsınız.',
                'features' => [
                    'Marka' => 'DMV Corporation',
                    'Menşei' => 'ABD (Orijinal)',
                    'Tasarım' => 'Havalandırmalı (Vented) çıkarma kanalı',
                    'Uyumluluk' => 'Skleral lensler ve büyük çaplı lensler',
                    'Malzeme' => 'Yumuşak tıbbi silikon'
                ],
                'image_path' => 'img/dmv plungerler/dmvventedskelralcup.jpg',
                'meta_title' => 'DMV Vented Scleral Cup Havalandırmalı Skleral Vantuz',
                'meta_description' => 'Havalandırmalı DMV Vented Scleral Cup ile skleral lenslerinizi hasar vermeden kolayca çıkarın. DMV Türkiye yetkili satıcısı.'
            ],
            [
                'category_slug' => 'dmv-urunleri',
                'name' => 'DMV Scleral Stand Skleral Lens Standı',
                'price' => 185.00,
                'stock' => 0,
                'rating' => 4.8,
                'description' => 'DMV Scleral Stand, skleral lens takma işlemini her zamankinden çok daha pratik ve hijyenik hale getiren özel tasarım lens standıdır. Lens solüsyonu dolu şekilde lensi sabitleyen stand yapısı sayesinde iki eliniz serbest kalır ve lensi çok daha rahat takarsınız.',
                'features' => [
                    'Marka' => 'DMV Corporation',
                    'Menşei' => 'ABD (Orijinal İthal)',
                    'Ürün Tipi' => 'Skleral lens takma standı',
                    'Kullanım' => 'Skleral ve büyük çaplı kontakt lensler',
                    'Özellik' => 'Çift el serbest bırakma, solüsyon hazneli'
                ],
                'image_path' => 'img/dmv plungerler/dmvscleralstand.jpg',
                'meta_title' => 'DMV Scleral Stand Skleral Lens Takma Standı',
                'meta_description' => 'DMV Scleral Stand ile skleral lenslerinizi iki eliniz serbest şekilde, kolayca ve hijyenik olarak takın. DMV Türkiye yetkili satıcısı.'
            ],
            [
                'category_slug' => 'dmv-urunleri',
                'name' => 'DMV Classic Sert Lens Takma ve Çıkarma Aparatı',
                'price' => 180.00,
                'discount_price' => 159.00,
                'stock' => 0,
                'rating' => 4.8,
                'description' => 'Orijinal DMV Corporation ABD üretimi olan DMV Classic, sert gaz geçirgen (RGP) ve yumuşak kontakt lensleri göze temas etmeden güvenle takıp çıkarmak için tasarlanmış özel bir vantuzdur. Doğal tıbbi kauçuktan üretilmiştir, göz sağlığınıza zarar vermez. Firmamız DMV ürünlerinin yetkili satıcısıdır.',
                'features' => [
                    'Marka' => 'DMV Corporation',
                    'Menşei' => 'ABD (Orijinal İthal)',
                    'Ürün Tipi' => 'Kontakt lens takma ve çıkarma aparatı',
                    'Malzeme' => 'Doğal tıbbi kauçuk (Lateks içermez)',
                    'Uyumluluk' => 'Sert gaz geçirgen (RGP) lensler, protez gözler ve skleral lensler',
                    'Garanti' => 'Yetkili Satıcı Orijinallik Garantili'
                ],
                'image_path' => 'img/dmv plungerler/dmvclassic.jpg',
                'meta_title' => 'DMV Classic Sert Lens Aparatı — Yetkili Satıcı',
                'meta_description' => 'Orijinal ABD üretimi DMV Classic kontakt lens takma ve çıkarma aparatı. Yetkili satıcı güvencesi ve hızlı kargo ile hemen sipariş verin.'
            ],
            [
                'category_slug' => 'dmv-urunleri',
                'name' => 'DMV Soft Lens Handler Yumuşak Lens Aparatı',
                'price' => 195.00,
                'stock' => 0,
                'rating' => 4.7,
                'description' => 'DMV Soft Lens Handler, günümüzün en çok satan yumuşak (soft) kontakt lens aparatlarından biridir. 4 farklı renk seçeneğiyle sunulan bu model, ergonomik tutamak yapısı ve silikon uç tasarımı ile lens takmak ve çıkarmak hiç bu kadar pratik olmamıştır.',
                'features' => [
                    'Marka' => 'DMV Corporation',
                    'Menşei' => 'ABD (Orijinal)',
                    'Renk Seçenekleri' => '4 farklı renk',
                    'Ürün Tipi' => 'Soft lens takma-çıkarma aparatı',
                    'Malzeme' => 'Silikon uç, ergonomik plastik sap',
                    'Uyumluluk' => 'Tüm yumuşak kontakt lensler'
                ],
                'image_path' => 'img/dmv plungerler/dmvsoftlenshandler.jpg',
                'meta_title' => 'DMV Soft Lens Handler Yumuşak Lens Aparatı — 4 Renk',
                'meta_description' => 'DMV Soft Lens Handler yumuşak kontakt lens aparatı. 4 renk seçeneği ile lenslerinizi güvenle yönetin. DMV Türkiye yetkili satıcısı.'
            ],
            [
                'category_slug' => 'dmv-urunleri',
                'name' => 'DMV 45 Sert Lens Aparatı (45° Açılı)',
                'price' => 210.00,
                'stock' => 0,
                'rating' => 4.8,
                'description' => 'DMV 45, 45 derecelik özel açısıyla sert (RGP) kontakt lenslerin özellikle alt çıkarma manevralarında üstün ergonomi sağlar. Yaşlı bireyler ve bilek hareket kısıtlılığı olan kullanıcılar için idealdir.',
                'features' => [
                    'Marka' => 'DMV Corporation',
                    'Menşei' => 'ABD (Orijinal)',
                    'Açı' => '45 derece eğimli aparat',
                    'Uyumluluk' => 'Sert (RGP) kontakt lensler',
                    'Özellik' => 'Bilek dostu ergonomik açı tasarımı'
                ],
                'image_path' => 'img/dmv plungerler/45-1.jpg',
                'meta_title' => 'DMV 45 Derece Sert Lens Aparatı',
                'meta_description' => '45 derecelik yenilikçi tasarımıyla DMV 45 aparatı ile sert lenslerinizi rahatça çıkarın. DMV Türkiye yetkili satıcısı.'
            ],
            [
                'category_slug' => 'dmv-urunleri',
                'name' => 'DMV Lumaserter Plus Yumuşak Lens Vantuzu',
                'price' => 199.00,
                'discount_price' => 179.00,
                'stock' => 0,
                'rating' => 4.7,
                'description' => 'DMV Lumaserter Plus, yumuşak kontakt lens kullananlar için hafif vakumlu silikon yapısıyla lensinizi gözünüzden nazikçe çeken özel tasarım bir vantuzlu aparattır. Gözünüze tırnağınızın temas etmesini önleyerek enfeksiyon ve tahriş riskini ortadan kaldırır.',
                'features' => [
                    'Marka' => 'DMV Corporation',
                    'Menşei' => 'ABD (Orijinal)',
                    'Ürün Tipi' => 'Yumuşak lens çıkarma vantuzu',
                    'Malzeme' => 'Yumuşak tıbbi silikon',
                    'Özellik' => 'Hafif vakum kanallı kavrama ucu'
                ],
                'image_path' => 'img/dmv plungerler/dmvlumaserterplus.jpg',
                'meta_title' => 'DMV Lumaserter Plus Yumuşak Lens Vantuzu',
                'meta_description' => 'Yumuşak kontakt lensleri kolayca çıkarmak için DMV Lumaserter Plus silikon vakumlu vantuz. Yetkili satıcı garantisiyle.'
            ],
            [
                'category_slug' => 'dmv-urunleri',
                'name' => 'DMV Versa Kontakt Lens Aparatı',
                'price' => 190.00,
                'stock' => 0,
                'rating' => 4.7,
                'description' => 'DMV Versa, sert ve yumuşak her iki lens tipinde kullanılabilen çok yönlü bir kontakt lens aparatıdır. Kompakt yapısı sayesinde her çantaya sığar, seyahat dostu tasarımı ile her ortamda hijyenik kullanım sunar.',
                'features' => [
                    'Marka' => 'DMV Corporation',
                    'Menşei' => 'ABD (Orijinal)',
                    'Ürün Tipi' => 'Çok yönlü kontakt lens aparatı',
                    'Malzeme' => 'Tıbbi silikon',
                    'Uyumluluk' => 'Sert ve yumuşak kontakt lensler'
                ],
                'image_path' => 'img/dmv plungerler/dmvversa.jpg',
                'meta_title' => 'DMV Versa Kontakt Lens Aparatı',
                'meta_description' => 'Sert ve yumuşak her iki lens tipinde kullanılabilen DMV Versa çok yönlü kontakt lens aparatı. DMV Türkiye yetkili satıcısı.'
            ]
        ];

        foreach ($products as $prod) {
            $catSlug = $prod['category_slug'];
            unset($prod['category_slug']);
            $prod['category_id'] = $createdCategories[$catSlug]->id;
            $prod['slug'] = Str::slug($prod['name']);
            
            // Yazım kolaylığı için ürünler "price = liste fiyatı,
            // discount_price = satış fiyatı" biçiminde tanımlanmış.
            // Şemada tek fiyat alanı var: liste fiyatı eski_fiyat'a taşınır,
            // satış fiyatı price olur. discount_price kaydedilmez.
            if (isset($prod['discount_price']) && $prod['discount_price'] !== null) {
                $prod['eski_fiyat'] = $prod['price'];
                $prod['price']      = $prod['discount_price'];
            }

            unset($prod['discount_price']);
            
            // Assign a random sales count for demo purposes
            $prod['satis_sayisi'] = rand(10, 180);

            // Dynamically assign additional images if they exist in the folders
            if (Str::contains(Str::lower($prod['name']), 'beetle')) {
                $prod['additional_images'] = [
                    'img/dfr1117/dfr1117-2.jpg',
                    'img/dfr1117/dfr1117-3.jpg'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'esp32-c6-devkit') || Str::contains(Str::lower($prod['name']), 'esp32-c6-1-n8')) {
                $prod['additional_images'] = [
                    'img/esp32c61n8/esp32c61n8-2.jpg'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'supermini') && Str::contains(Str::lower($prod['name']), 'c3')) {
                $prod['additional_images'] = [
                    'img/esp32 c3 süper min/esp32c3mini1.jpeg',
                    'img/esp32 c3 süper min/esp32c3mini2.jpeg',
                    'img/esp32 c3 süper min/1.webp',
                    'img/esp32 c3 süper min/2.webp'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'dmv ultra')) {
                $prod['additional_images'] = [
                    'img/dmv plungerler/dmvultra2.jpg',
                    'img/dmv plungerler/dmvultra3.jpg',
                    'img/dmv plungerler/dmvultra4.jpg',
                    'img/dmv plungerler/dmvultra6.jpg',
                    'img/dmv plungerler/dmvultra7.jpg'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'dmv 45')) {
                $prod['additional_images'] = [
                    'img/dmv plungerler/45-2.jpg',
                    'img/dmv plungerler/45-3.jpg',
                    'img/dmv plungerler/45-4.jpg'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'scleral cup')) {
                $prod['additional_images'] = [
                    'img/dmv plungerler/skleral1.jpg',
                    'img/dmv plungerler/skleral2.jpg',
                    'img/dmv plungerler/skleral3.jpg',
                    'img/dmv plungerler/skleral4.jpg'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'vented scleral') || Str::contains(Str::lower($prod['name']), 'havalandırmalı skleral')) {
                $prod['additional_images'] = [
                    'img/dmv plungerler/vented-scleral-cup3.jpg',
                    'img/dmv plungerler/vented-scleral-cup4.jpg',
                    'img/dmv plungerler/vented-scleral-cup5.jpg',
                    'img/dmv plungerler/vented-scleral-cup7.jpg'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'scleral stand')) {
                $prod['additional_images'] = [
                    'img/dmv plungerler/scleral-stand2.jpg',
                    'img/dmv plungerler/scleral-stand3-1.jpg'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'soft lens handler')) {
                $prod['additional_images'] = [
                    'img/dmv plungerler/skleral1.jpg'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'lumaserter plus')) {
                $prod['additional_images'] = [
                    'img/dmv plungerler/LumaSerta3.jpg',
                    'img/dmv plungerler/lumaserta4.jpg',
                    'img/dmv plungerler/LumaSerta7.jpg'
                ];
            } elseif (Str::contains(Str::lower($prod['name']), 'versa')) {
                $prod['additional_images'] = [
                    'img/dmv plungerler/versa1.jpg'
                ];
            }
            
            Product::create($prod);
        }
    }
}
