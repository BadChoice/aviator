<?php

namespace App\Services\AppStore\Helpers;

class SalesResponse
{

    static function tsvToArray(string $tsv): array
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($tsv));
        if (count($lines) < 2) {
            return [];
        }

        // Remove BOM if present
        $lines[0] = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0]);

        $headers = str_getcsv(array_shift($lines), "\t");
        $headerCount = count($headers);

        $rows = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue; // skip empty lines
            }

            $values = str_getcsv($line, "\t");

            // Normalize column count
            $valueCount = count($values);

            if ($valueCount < $headerCount) {
                $values = array_pad($values, $headerCount, null);
            } elseif ($valueCount > $headerCount) {
                $values = array_slice($values, 0, $headerCount);
            }

            $rows[] = array_combine($headers, $values);
        }

        return $rows;
    }
}
