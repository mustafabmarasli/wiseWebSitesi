<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class Csv
{
    /**
     * Satır dizisinden indirilebilir bir CSV yanıtı üretir.
     *
     * Livewire/Filament action'larından doğrudan `return` edilebilir.
     */
    public static function download(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            // Excel'in UTF-8 olarak açması için BOM
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($handle, array_map(self::neutralize(...), (array) $row));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * CSV formül enjeksiyonunu engeller.
     *
     * "=", "+", "-", "@" ve tab/CR ile başlayan hücreler Excel/Sheets tarafından
     * formül olarak yorumlanır; başına tek tırnak konarak metne zorlanır.
     */
    private static function neutralize(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if ($value !== '' && str_contains("=+-@\t\r", $value[0])) {
            return "'" . $value;
        }

        return $value;
    }
}
