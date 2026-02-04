<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportsExport
{
    protected $data;
    protected $headings;

    public function __construct($data, $headings = [])
    {
        $this->data = $data;
        $this->headings = $headings;
    }

    public function download($filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $rows = $this->data;
        $maxCols = 0;
        foreach ($rows as $row) {
            $maxCols = max($maxCols, is_array($row) ? count($row) : 0);
        }

        $maxCols = max($maxCols, 1);
        $lastColLetter = self::colLetter($maxCols);

        // Add data to spreadsheet
        $rowNum = 1;
        foreach ($rows as $row) {
            $row = is_array($row) ? array_values($row) : [];
            for ($colIndex = 0; $colIndex < $maxCols; $colIndex++) {
                $cellValue = $row[$colIndex] ?? '';
                $cellCoord = self::colLetter($colIndex + 1) . $rowNum;
                $sheet->setCellValue($cellCoord, $cellValue);
            }
            $rowNum++;
        }

        // Apply styling
        $highestRow = $sheet->getHighestRow();
        
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell("A{$row}")->getValue();
            $rowRange = "A{$row}:{$lastColLetter}{$row}";
            $colB = $sheet->getCell("B{$row}")->getValue();
            $colC = $sheet->getCell("C{$row}")->getValue();
            
            // Section title rows: first cell not empty, other cells empty
            if ($cellValue && trim((string) $cellValue) !== '' && (empty($colB) || trim((string) $colB) === '') && (empty($colC) || trim((string) $colC) === '')) {
                $sheet->getStyle($rowRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '3B82F6'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'border' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);
            }
            // Header rows: all 3 cells are strings (not numeric)
            elseif (
                $cellValue && trim((string) $cellValue) !== ''
                && $colB && trim((string) $colB) !== ''
                && $colC && trim((string) $colC) !== ''
                && !is_numeric($colB)
                && !is_numeric(str_replace('%', '', (string) $colC))
            ) {
                $sheet->getStyle($rowRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 10,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2563EB'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'border' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);
            }
            // Empty rows - skip
            elseif (!$cellValue || trim($cellValue) === '') {
                // Do nothing
            }
            // Data rows
            else {
                $sheet->getStyle($rowRange)->applyFromArray([
                    'border' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'DDDDDD'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Alternate row colors
                if ($row % 2 == 0) {
                    $sheet->getStyle($rowRange)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F9FAFB');
                }
            }
        }

        // Set column widths (first column wider, others standard)
        $sheet->getColumnDimension('A')->setWidth(30);
        for ($i = 2; $i <= $maxCols; $i++) {
            $sheet->getColumnDimension(self::colLetter($i))->setWidth(18);
        }

        // Write to a temp file then download (avoids any output-buffer corruption)
        $writer = new Xlsx($spreadsheet);

        $tmpFile = tempnam(sys_get_temp_dir(), 'hospital-reports-');
        if ($tmpFile === false) {
            abort(500, __('messages.failed_create_temp_file_for_excel_export'));
        }

        // Ensure no buffered output contaminates the file stream
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend(true);
    }

    private static function colLetter(int $index): string
    {
        // 1 -> A, 26 -> Z, 27 -> AA
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intdiv($index - 1, 26);
        }
        return $letters;
    }
}
