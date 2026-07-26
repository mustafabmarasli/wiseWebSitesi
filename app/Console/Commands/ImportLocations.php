<?php

namespace App\Console\Commands;

use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ImportLocations extends Command
{
    protected $signature = 'locations:import
                            {--fresh : Mevcut tabloları silip sıfırdan içe aktar}';

    protected $description = 'İl, ilçe ve mahalle verilerini SQL dosyalarından içe aktarır';

    /** dosya => [tablo adı, etiket] */
    private const FILES = [
        'provinces.sql'     => ['provinces', 'İller'],
        'districts.sql'     => ['districts', 'İlçeler'],
        'neighborhoods.sql' => ['neighborhoods', 'Mahalleler'],
    ];

    public function handle(): int
    {
        DB::connection()->disableQueryLog();

        $this->tryStatement('SET FOREIGN_KEY_CHECKS=0;');

        // --fresh: tabloları önce topluca düşür. Tek tek düşürmek yetmiyor;
        // districts ve neighborhoods provinces'a yabancı anahtarla bağlı olduğu
        // için provinces yeniden oluşturulamıyordu (errno 150).
        if ($this->option('fresh')) {
            foreach (['neighborhoods', 'districts', 'provinces'] as $table) {
                $this->tryStatement("DROP TABLE IF EXISTS `{$table}`;");
            }
        }

        $hadError = false;

        foreach (self::FILES as $filename => [$table, $label]) {
            if (!$this->importFile($filename, $table, $label)) {
                $hadError = true;
            }
        }

        $this->tryStatement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->line('Sonuç:');

        foreach (self::FILES as [$table, $label]) {
            $exists = Schema::hasTable($table);
            $this->line(sprintf(
                '  %-12s %s',
                $label . ':',
                $exists
                    ? '<fg=green>' . number_format(DB::table($table)->count()) . ' kayıt</>'
                    : '<fg=red>TABLO YOK</>'
            ));

            if (!$exists) {
                $hadError = true;
            }
        }

        $this->newLine();

        if ($hadError) {
            $this->error('İçe aktarma tamamlanamadı — yukarıdaki hatalara bakın.');

            return Command::FAILURE;
        }

        $this->info('Konum verileri içe aktarıldı.');

        return Command::SUCCESS;
    }

    private function importFile(string $filename, string $table, string $label): bool
    {
        $path = base_path($filename);

        if (!File::exists($path)) {
            $this->error("{$label}: {$filename} bulunamadı.");

            return false;
        }

        // --fresh değilse ve tablo doluysa dokunma
        if (Schema::hasTable($table)) {
            $this->line("{$label}: tablo zaten var, atlanıyor (yeniden yüklemek için --fresh).");

            return true;
        }

        $this->info("{$label} içe aktarılıyor...");

        $executed = 0;
        $bar      = $this->output->createProgressBar();
        $bar->start();

        try {
            // Dosyanın tamamını tek pakette göndermek max_allowed_packet sınırını
            // aşıyordu (mahalleler ~6.8 MB); ifadeler tek tek çalıştırılır.
            foreach ($this->statements(File::get($path)) as $statement) {
                DB::unprepared($statement);
                $executed++;
                $bar->advance();
            }
        } catch (\Throwable $e) {
            $bar->finish();
            $this->newLine();
            $this->error("{$label}: " . str($e->getMessage())->limit(180));

            return false;
        }

        $bar->finish();
        $this->newLine();
        $this->line("  {$executed} ifade çalıştırıldı.");

        return true;
    }

    /**
     * SQL dökümünü tek tek çalıştırılabilir ifadelere böler.
     *
     * Noktalı virgül metin değerlerinin içinde de geçebileceği için basit bir
     * explode(';') yeterli değildir; tırnak durumu takip edilir.
     *
     * @return Generator<string>
     */
    private function statements(string $sql): Generator
    {
        $buffer     = '';
        $inString   = false;
        $stringChar = '';
        $length     = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($inString) {
                $buffer .= $char;

                // Ters bölü bir sonraki karakteri kaçırır
                if ($char === '\\' && $i + 1 < $length) {
                    $buffer .= $sql[++$i];

                    continue;
                }

                if ($char === $stringChar) {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'" || $char === '"') {
                $inString   = true;
                $stringChar = $char;
                $buffer    .= $char;

                continue;
            }

            if ($char === ';') {
                if ($statement = $this->clean($buffer)) {
                    yield $statement;
                }

                $buffer = '';

                continue;
            }

            $buffer .= $char;
        }

        if ($statement = $this->clean($buffer)) {
            yield $statement;
        }
    }

    /**
     * Yorum satırlarını temizler; çalıştırılacak bir şey kalmazsa null döner.
     */
    private function clean(string $statement): ?string
    {
        $lines = [];

        foreach (preg_split('/\R/', $statement) as $line) {
            $trimmed = trim($line);

            // phpMyAdmin başlık yorumları ve sürüm koşullu direktifler
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            $lines[] = $line;
        }

        $result = trim(implode("\n", $lines));

        return $result === '' ? null : $result;
    }

    private function tryStatement(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable) {
            // Sürücü desteklemiyorsa sessizce geç
        }
    }
}
