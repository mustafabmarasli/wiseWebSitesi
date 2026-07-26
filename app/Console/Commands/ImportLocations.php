<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import provinces, districts, and neighborhoods from SQL files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = [
            'provinces.sql' => 'İller (Provinces)',
            'districts.sql' => 'İlçeler (Districts)',
            'neighborhoods.sql' => 'Mahalleler (Neighborhoods)',
        ];

        $this->info('Konum verileri içe aktarılıyor...');

        // Disable query log to prevent memory leaks
        DB::connection()->disableQueryLog();
        
        // Disable foreign key checks for the session
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } catch (\Exception $e) {
            // Ignore if driver doesn't support it
        }

        foreach ($files as $filename => $label) {
            $path = base_path($filename);

            if (!File::exists($path)) {
                $this->error("Hata: {$filename} dosyası bulunamadı! Yol: {$path}");
                continue;
            }

            $this->info("İçe aktarılıyor: {$label} ({$filename})...");
            
            try {
                $sql = File::get($path);
                
                // Execute sql statements
                DB::unprepared($sql);
                
                $this->info("Başarılı: {$label} içe aktarıldı.");
            } catch (\Exception $e) {
                $this->error("Hata ({$label} içe aktarılırken): " . $e->getMessage());
            }
        }

        // Re-enable foreign key checks
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            // Ignore
        }

        $this->info('Tüm konum verileri başarıyla içe aktarıldı!');
        return Command::SUCCESS;
    }
}
