<?php

namespace App\Helpers;

use ZipArchive;
use SimpleXMLElement;
use Exception;

class XlsxParser
{
    /**
     * Parse an .xlsx file and return its rows.
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     */
    public static function parse(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Gagal membuka file Excel.");
        }

        // 1. Read shared strings if they exist
        $sharedStrings = [];
        $sharedStringsEntry = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsEntry) {
            $xml = new SimpleXMLElement($sharedStringsEntry);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                    $sharedStrings[] = $text;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }

        // 2. Read sheet1
        $sheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetData) {
            $zip->close();
            throw new Exception("Worksheet data tidak ditemukan dalam berkas Excel.");
        }

        $xml = new SimpleXMLElement($sheetData);
        $rows = [];

        if (isset($xml->sheetData->row)) {
            foreach ($xml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $ref = (string)$cell['r']; // e.g. "A1", "B2"
                    preg_match('/^[A-Z]+/', $ref, $matches);
                    $colLetter = $matches[0] ?? '';
                    $colIndex = self::columnLetterToIndex($colLetter);

                    $val = '';
                    if (isset($cell->v)) {
                        $val = (string)$cell->v;
                        // If type is "s" (shared string)
                        if (isset($cell['t']) && (string)$cell['t'] === 's') {
                            $val = $sharedStrings[(int)$val] ?? '';
                        }
                    }
                    $rowData[$colIndex] = $val;
                }

                // Fill any missing middle cells with empty strings, sort by index
                if (!empty($rowData)) {
                    $maxIndex = max(array_keys($rowData));
                    for ($i = 0; $i <= $maxIndex; $i++) {
                        if (!isset($rowData[$i])) {
                            $rowData[$i] = '';
                        }
                    }
                    ksort($rowData);
                    $rows[] = $rowData;
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * Convert an Excel column letter (e.g. A, B, AA) to a 0-based index.
     *
     * @param string $letter
     * @return int
     */
    private static function columnLetterToIndex(string $letter): int
    {
        $index = 0;
        $len = strlen($letter);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letter[$i]) - 64);
        }
        return $index - 1;
    }
}
