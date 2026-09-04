<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Service for generating and streaming Excel and PDF report exports.
 */
class ReportExportService
{
    /**
     * Generate and stream an Excel (.xlsx) file to the browser.
     *
     * @param string $filename Base filename for the download
     * @param string $sheetTitle Sheet tab title
     * @param string $reportTitle Main report header title
     * @param list<string> $metadataLines Subtitle metadata lines
     * @param array<string, string> $headers Column header mapping (e.g., ['A' => 'No', 'B' => 'Name'])
     * @param list<array<string, mixed>> $rows Data matrix formatted by column letter (e.g., [['A' => 1, 'B' => 'Alpha']])
     * @param list<string> $centerColumns List of column letters to align center (e.g., ['A', 'B', 'D'])
     */
    public function exportExcel(
        string $filename,
        string $sheetTitle,
        string $reportTitle,
        array $metadataLines,
        array $headers,
        array $rows,
        array $centerColumns = []
    ): void {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($sheetTitle, 0, 31));

        $colKeys = array_keys($headers);
        $firstCol = reset($colKeys) ?: 'A';
        $lastCol = end($colKeys) ?: 'A';

        // 1. Report Title Header
        $sheet->setCellValue($firstCol . '1', $reportTitle);
        $sheet->mergeCells("{$firstCol}1:{$lastCol}1");
        $sheet->getStyle("{$firstCol}1")->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E1E2D');
        $sheet->getStyle("{$firstCol}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // 2. Metadata Subtitle Lines
        $metaRow = 2;
        foreach ($metadataLines as $line) {
            $sheet->setCellValue($firstCol . $metaRow, $line);
            $sheet->mergeCells("{$firstCol}{$metaRow}:{$lastCol}{$metaRow}");
            $fontStyle = $sheet->getStyle($firstCol . $metaRow)->getFont();
            $fontStyle->setSize(9)->getColor()->setRGB('6C757D');
            if ($metaRow === 2) {
                $fontStyle->setItalic(true);
            }
            $metaRow++;
        }

        // 3. Table Column Headers
        $headerRow = 5;
        foreach ($headers as $col => $headerText) {
            $sheet->setCellValue($col . $headerRow, $headerText);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '435EBE'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("{$firstCol}{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray($headerStyle);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        // 4. Data Rows
        $currentRow = 6;
        foreach ($rows as $rowData) {
            foreach ($rowData as $col => $val) {
                $sheet->setCellValue($col . $currentRow, $val ?? '-');
            }

            foreach ($centerColumns as $cCol) {
                $sheet->getStyle($cCol . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $currentRow++;
        }

        $lastRow = max($headerRow + 1, $currentRow - 1);

        // 5. Borders & Auto-sizing
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDE2E5'],
                ],
            ],
        ];
        $sheet->getStyle("{$firstCol}{$headerRow}:{$lastCol}{$lastRow}")->applyFromArray($borderStyle);

        foreach ($colKeys as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $cleanFilename = str_ends_with(strtolower($filename), '.xlsx') ? $filename : $filename . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $cleanFilename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Render a view and stream as a PDF download to the browser.
     *
     * @param string $filename Base filename for the download
     * @param string $viewPath View template path
     * @param array<string, mixed> $viewData Data array to pass to the view
     * @param string $paperSize Paper size (default: 'A4')
     * @param string $orientation Page orientation ('portrait' or 'landscape')
     */
    public function exportPdf(
        string $filename,
        string $viewPath,
        array $viewData,
        string $paperSize = 'A4',
        string $orientation = 'landscape'
    ): void {
        $html = view($viewPath, $viewData);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paperSize, $orientation);
        $dompdf->render();

        $cleanFilename = str_ends_with(strtolower($filename), '.pdf') ? $filename : $filename . '.pdf';
        $dompdf->stream($cleanFilename, ['Attachment' => true]);
        exit;
    }
}
