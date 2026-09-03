<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\CriticalityRecoveryModel;
use App\Models\UserModel;
use Throwable;

class Application extends BaseController
{
    protected ApplicationModel $applicationModel;
    protected CriticalityRecoveryModel $criticalityRecoveryModel;
    protected UserModel $userModel;

    public function __construct()
    {
        helper(['form']);
        $this->applicationModel = new ApplicationModel();
        $this->criticalityRecoveryModel = new CriticalityRecoveryModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $criticality = $this->request->getGet('criticality_recovery_id');
        $criticality = is_numeric($criticality) ? (int) $criticality : null;

        return view('application/index', [
            'title' => 'Aplikasi Pengelolaan',
            'applications' => $this->applicationModel->getApplicationsWithDetails($criticality, $keyword ?: null),
            'criticalityOptions' => $this->criticalityRecoveryModel->getActiveOptions(),
            'keyword' => $keyword,
            'selectedCriticality' => $criticality,
        ]);
    }

    public function create()
    {
        return view('application/create', $this->formData(null));
    }

    public function detail($id)
    {
        $application = $this->applicationModel->getApplicationDetail((int) $id);
        if (!$application) {
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak ditemukan.');
        }

        return view('application/detail', ['title' => 'Detail Aplikasi', 'application' => $application]);
    }

    public function edit($id)
    {
        $application = $this->applicationModel->find((int) $id);
        if (!$application) {
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak ditemukan.');
        }

        return view('application/edit', $this->formData($application));
    }

    public function store()
    {
        $payload = $this->validatedPayload();
        if ($payload === null) {
            return redirect()->to('/aplikasi/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            if (!$this->applicationModel->insert($payload)) {
                return redirect()->to('/aplikasi/create')->withInput()->with('error', 'Aplikasi gagal disimpan. Silakan periksa data dan coba lagi.');
            }
        } catch (Throwable $exception) {
            log_message('error', 'Gagal menyimpan aplikasi: {message}', ['message' => $exception->getMessage()]);
            return redirect()->to('/aplikasi/create')->withInput()->with('error', 'Terjadi gangguan saat menyimpan aplikasi.');
        }

        return redirect()->to('/aplikasi')->with('success', 'Aplikasi berhasil ditambahkan.');
    }

    public function update($id)
    {
        $id = (int) $id;
        if (!$this->applicationModel->find($id)) {
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak ditemukan.');
        }

        $payload = $this->validatedPayload();
        if ($payload === null) {
            return redirect()->to('/aplikasi/edit/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            if (!$this->applicationModel->update($id, $payload)) {
                return redirect()->to('/aplikasi/edit/' . $id)->withInput()->with('error', 'Aplikasi gagal diperbarui.');
            }
        } catch (Throwable $exception) {
            log_message('error', 'Gagal memperbarui aplikasi {id}: {message}', ['id' => $id, 'message' => $exception->getMessage()]);
            return redirect()->to('/aplikasi/edit/' . $id)->withInput()->with('error', 'Terjadi gangguan saat memperbarui aplikasi.');
        }

        return redirect()->to('/aplikasi/detail/' . $id)->with('success', 'Aplikasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $id = (int) $id;
        if (!$this->applicationModel->find($id)) {
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak ditemukan.');
        }

        try {
            if (!$this->applicationModel->delete($id)) {
                return redirect()->to('/aplikasi')->with('error', 'Aplikasi gagal dihapus.');
            }
        } catch (Throwable $exception) {
            log_message('error', 'Gagal menghapus aplikasi {id}: {message}', ['id' => $id, 'message' => $exception->getMessage()]);
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak dapat dihapus karena masih digunakan atau terjadi gangguan database.');
        }
        return redirect()->to('/aplikasi')->with('success', 'Aplikasi berhasil dihapus.');
    }

    private function formData(?array $application): array
    {
        return [
            'title' => $application ? 'Edit Aplikasi' : 'Tambah Aplikasi',
            'application' => $application,
            'criticalityOptions' => $this->criticalityRecoveryModel->getActiveOptions(),
            'users' => $this->userModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
            'formAction' => base_url($application ? '/aplikasi/update/' . $application['id'] : '/aplikasi/store'),
            'submitLabel' => $application ? 'Simpan Perubahan' : 'Simpan Aplikasi',
        ];
    }

    /** Validate references and normalize all user-editable fields before persistence. */
    private function validatedPayload(): ?array
    {
        $rules = [
            'app_component' => 'required|max_length[150]',
            'criticality_recovery_id' => 'required|is_natural_no_zero',
            'assigned_user_id' => 'permit_empty|is_natural_no_zero',
            'url_prod' => 'permit_empty|valid_url',
            'url_dev' => 'permit_empty|valid_url',
            'url_uat' => 'permit_empty|valid_url',
        ];
        if (!$this->validate($rules)) {
            return null;
        }

        $criticality = (int) $this->request->getPost('criticality_recovery_id');
        $pic = $this->request->getPost('assigned_user_id');
        if (!$this->criticalityRecoveryModel->where('id', $criticality)->where('is_active', 1)->first()) {
            $this->validator->setError('criticality_recovery_id', 'Criticality yang dipilih tidak valid.');
            return null;
        }
        if ($pic !== null && $pic !== '' && !$this->userModel->where('id', (int) $pic)->where('is_active', 1)->first()) {
            $this->validator->setError('assigned_user_id', 'PIC yang dipilih tidak valid.');
            return null;
        }

        $fields = ['app_component', 'description', 'app_type', 'arch_type', 'access_type', 'login_auth', 'platform', 'url_prod', 'url_dev', 'url_uat', 'development_type', 'vendor', 'license_scheme', 'deployment_type', 'business_owner', 'system_owner'];
        $payload = [];
        foreach ($fields as $field) {
            $value = trim((string) $this->request->getPost($field));
            $payload[$field] = $value !== '' ? $value : null;
        }
        $payload['criticality_recovery_id'] = $criticality;
        $payload['assigned_user_id'] = ($pic !== null && $pic !== '') ? (int) $pic : null;
        $payload['has_source_code'] = $this->request->getPost('has_source_code') ? 1 : 0;

        return $payload;
    }

    public function exportExcel()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $criticality = $this->request->getGet('criticality_recovery_id');
        $criticality = is_numeric($criticality) ? (int) $criticality : null;

        $applications = $this->applicationModel->getApplicationsWithDetails($criticality, $keyword ?: null);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Aplikasi');

        // Header Title
        $sheet->setCellValue('A1', 'MASTER DATA APLIKASI PENGELOLAAN');
        $sheet->mergeCells('A1:T1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E1E2D');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Metadata
        $critObj = !empty($criticality) ? $this->criticalityRecoveryModel->find($criticality) : null;
        $critText = $critObj['criticality_name'] ?? 'Semua Tingkat Criticality';

        $sheet->setCellValue('A2', 'Criticality: ' . $critText . ' | Pencarian: ' . (!empty($keyword) ? $keyword : '-'));
        $sheet->mergeCells('A2:T2');
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('6C757D');

        $sheet->setCellValue('A3', 'Dicetak pada: ' . date('d M Y, H:i') . ' WIB | Dicetak oleh: ' . (session()->get('name') ?? 'User') . ' | Total: ' . count($applications) . ' Aplikasi');
        $sheet->mergeCells('A3:T3');
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('6C757D');

        // Column Headers
        $headers = [
            'A5' => 'No',
            'B5' => 'Nama Aplikasi',
            'C5' => 'Criticality Recovery',
            'D5' => 'Tipe Aplikasi',
            'E5' => 'Arsitektur',
            'F5' => 'Platform',
            'G5' => 'Tipe Akses',
            'H5' => 'Autentikasi Login',
            'I5' => 'Tipe Pengembangan',
            'J5' => 'Tipe Deployment',
            'K5' => 'Skema Lisensi',
            'L5' => 'Vendor',
            'M5' => 'Business Owner',
            'N5' => 'System Owner',
            'O5' => 'Assigned PIC',
            'P5' => 'Source Code',
            'Q5' => 'URL Production',
            'R5' => 'URL Development',
            'S5' => 'URL UAT',
            'T5' => 'Deskripsi',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '435EBE'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A5:T5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Data Rows
        $row = 6;
        $no = 1;
        foreach ($applications as $app) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $app['app_component'] ?? '-');
            $sheet->setCellValue('C' . $row, $app['criticality_recovery'] ?? '-');
            $sheet->setCellValue('D' . $row, $app['app_type'] ?? '-');
            $sheet->setCellValue('E' . $row, $app['arch_type'] ?? '-');
            $sheet->setCellValue('F' . $row, $app['platform'] ?? '-');
            $sheet->setCellValue('G' . $row, $app['access_type'] ?? '-');
            $sheet->setCellValue('H' . $row, $app['login_auth'] ?? '-');
            $sheet->setCellValue('I' . $row, $app['development_type'] ?? '-');
            $sheet->setCellValue('J' . $row, $app['deployment_type'] ?? '-');
            $sheet->setCellValue('K' . $row, $app['license_scheme'] ?? '-');
            $sheet->setCellValue('L' . $row, $app['vendor'] ?? '-');
            $sheet->setCellValue('M' . $row, $app['business_owner'] ?? '-');
            $sheet->setCellValue('N' . $row, $app['system_owner'] ?? '-');
            $sheet->setCellValue('O' . $row, $app['assigned_user_name'] ?? '-');
            $sheet->setCellValue('P' . $row, ((int) ($app['has_source_code'] ?? 0) === 1) ? 'Ada' : 'Tidak');
            $sheet->setCellValue('Q' . $row, $app['url_prod'] ?? '-');
            $sheet->setCellValue('R' . $row, $app['url_dev'] ?? '-');
            $sheet->setCellValue('S' . $row, $app['url_uat'] ?? '-');
            $sheet->setCellValue('T' . $row, $app['description'] ?? '-');

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row . ':K' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('P' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        $lastRow = max(6, $row - 1);

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDE2E5'],
                ],
            ],
        ];
        $sheet->getStyle('A5:T' . $lastRow)->applyFromArray($borderStyle);

        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Master_Aplikasi_Pengelolaan_' . date('Ymd_His') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportPdf()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $criticality = $this->request->getGet('criticality_recovery_id');
        $criticality = is_numeric($criticality) ? (int) $criticality : null;

        $applications = $this->applicationModel->getApplicationsWithDetails($criticality, $keyword ?: null);

        $critObj = !empty($criticality) ? $this->criticalityRecoveryModel->find($criticality) : null;
        $critText = $critObj['criticality_name'] ?? 'Semua Tingkat Criticality';

        $html = view('application/export_pdf', [
            'reportTitle' => 'Laporan Master Aplikasi Pengelolaan',
            'applications' => $applications,
            'filterCriticalityLabel' => $critText,
            'keyword' => $keyword,
        ]);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Laporan_Master_Aplikasi_Pengelolaan_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}
