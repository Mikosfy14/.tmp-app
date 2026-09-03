<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ProjectFileModel;
use App\Models\ProjectStatusModel;
use App\Models\UserModel;

class Projects extends BaseController
{
    protected ProjectModel $projectModel;
    protected ProjectFileModel $projectFileModel;
    protected ProjectStatusModel $projectStatusModel;
    protected UserModel $userModel;

    public function __construct()
    {
        helper(['form']);
        $this->projectModel = new ProjectModel();
        $this->projectFileModel = new ProjectFileModel();
        $this->projectStatusModel = new ProjectStatusModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return $this->renderProjectList();
    }

    public function user($userId)
    {
        if (!$this->isKepalaDepartemen()) {
            return redirect()->to('/projects')->with('error', 'Akses ditolak. Hanya Kepala Departemen yang bisa melihat project user lain.');
        }

        return $this->renderProjectList((int) $userId);
    }

    public function create()
    {
        return view('projects/create', $this->getFormViewData([
            'title' => 'Tambah Project',
            'pageTitle' => 'Tambah Project',
            'pageSubtitle' => 'Buat project baru dengan status SDLC, timeline, PIC, dan file pendukung.',
            'formAction' => base_url('/projects/store'),
            'submitLabel' => 'Simpan Project',
            'project' => null,
            'selectedAssignedIds' => [(int) session()->get('user_id')],
        ]));
    }

    public function edit($id)
    {
        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), $this->isKepalaDepartemen());

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        return view('projects/edit', $this->getFormViewData([
            'title' => 'Edit Project - ' . $project['name'],
            'pageTitle' => 'Edit Project',
            'pageSubtitle' => 'Perbarui detail project, timeline, PIC, dan file pendukung.',
            'formAction' => base_url('/projects/update/' . $project['id']),
            'submitLabel' => 'Simpan Perubahan',
            'project' => $project,
            'selectedAssignedIds' => $this->parseAssignedToString($project['assigned_to'] ?? ''),
            'projectFiles' => $this->projectFileModel->getFilesByProject((int) $project['id']),
        ]));
    }

    public function detail($id)
    {
        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), $this->isKepalaDepartemen());

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        return view('projects/detail', [
            'title' => 'Detail Project - ' . $project['name'],
            'project' => $project,
            'projectFiles' => $this->projectFileModel->getFilesByProject((int) $project['id']),
        ]);
    }

    public function store()
    {
        $rules = $this->projectRules();

        if (!$this->validate($rules)) {
            return redirect()->to('/projects/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        $startDate = (string) $this->request->getPost('start_date');
        $promoteDate = (string) $this->request->getPost('promote_date');
        if (!empty($startDate) && !empty($promoteDate) && $promoteDate < $startDate) {
            return redirect()->to('/projects/create')->withInput()->with('errors', ['promote_date' => 'Tanggal Promote tidak boleh lebih awal dari Start Date.']);
        }

        $fileErrors = $this->validateUploadedFiles();
        if (!empty($fileErrors)) {
            return redirect()->to('/projects/create')->withInput()->with('errors', $fileErrors);
        }

        $projectId = $this->projectModel->insert($this->buildProjectPayload(), true);
        $this->storeUploadedFiles((int) $projectId);

        return redirect()->to('/projects')->with('success', 'Project berhasil ditambahkan!');
    }

    public function update($id)
    {
        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), $this->isKepalaDepartemen());

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $rules = $this->projectRules((int) $id);

        if (!$this->validate($rules)) {
            return redirect()->to('/projects/edit/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        $startDate = (string) $this->request->getPost('start_date');
        $promoteDate = (string) $this->request->getPost('promote_date');
        if (!empty($startDate) && !empty($promoteDate) && $promoteDate < $startDate) {
            return redirect()->to('/projects/edit/' . $id)->withInput()->with('errors', ['promote_date' => 'Tanggal Promote tidak boleh lebih awal dari Start Date.']);
        }

        $fileErrors = $this->validateUploadedFiles();
        if (!empty($fileErrors)) {
            return redirect()->to('/projects/edit/' . $id)->withInput()->with('errors', $fileErrors);
        }

        $this->projectModel->update($id, $this->buildProjectPayload($project));
        $this->storeUploadedFiles((int) $id);

        return redirect()->to('/projects/detail/' . $id)->with('success', 'Project berhasil diperbarui!');
    }

    public function updateProgress($id)
    {
        return $this->update($id);
    }

    public function delete($id)
    {
        if (!$this->canAccessProject((int) $id)) {
            return redirect()->to('/projects')->with('error', 'Akses ditolak. Anda tidak memiliki akses untuk menghapus project ini.');
        }

        $this->projectModel->delete($id);

        return redirect()->to('/projects')->with('success', 'Project berhasil dihapus!');
    }

    public function downloadFile($id)
    {
        $file = $this->projectFileModel->getFileForDownload((int) $id);

        if (!$file) {
            return redirect()->to('/projects')->with('error', 'File project tidak ditemukan.');
        }

        $project = $this->projectModel->getProjectDetail((int) $file['project_id'], (int) session()->get('user_id'), $this->isKepalaDepartemen());
        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Akses ditolak.');
        }

        return $this->response
            ->setHeader('Content-Type', $file['mime_type'] ?? 'application/octet-stream')
            ->setHeader('Content-Disposition', 'attachment; filename="' . ($file['original_name'] ?? 'project-file') . '"')
            ->setBody($file['file_data']);
    }

    public function deleteFile($id)
    {
        $file = $this->projectFileModel->getFileForDownload((int) $id);

        if (!$file) {
            return redirect()->to('/projects')->with('error', 'File project tidak ditemukan.');
        }

        $project = $this->projectModel->getProjectDetail((int) $file['project_id'], (int) session()->get('user_id'), $this->isKepalaDepartemen());
        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Akses ditolak.');
        }

        $this->projectFileModel->delete($id);

        return redirect()->to('/projects/edit/' . $project['id'])->with('success', 'File project berhasil dihapus.');
    }

    private function renderProjectList(?int $targetUserId = null)
    {
        $statusFilter = $this->request->getGet('status');
        $keyword = $this->request->getGet('keyword');
        $selectedStartDate = trim((string) $this->request->getGet('filter_start'));
        $selectedEndDate = trim((string) $this->request->getGet('filter_end'));
        $dateRange = $this->resolveProjectDateRange($selectedStartDate, $selectedEndDate);
        $selectedStartDate = $dateRange['start_date'] ?? '';
        $selectedEndDate = $dateRange['end_date'] ?? '';
        $includeAll = $this->isKepalaDepartemen() && $targetUserId === null;
        $userId = $targetUserId ?? (int) session()->get('user_id');
        $targetUser = $targetUserId ? $this->userModel->find($targetUserId) : null;

        if ($targetUserId && !$targetUser) {
            return redirect()->to('/projects')->with('error', 'User tidak ditemukan.');
        }

        $data = [
            'title' => 'Project Tracker',
            'projects' => $this->projectModel->getProjectsWithAssignees($statusFilter, $keyword, $userId, $includeAll, $dateRange),
            'pager' => $this->projectModel->pager,
            'users' => $this->userModel->where('is_active', 1)->findAll(),
            'selectedStatus' => $statusFilter,
            'keyword' => $keyword,
            'selectedStartDate' => $selectedStartDate,
            'selectedEndDate' => $selectedEndDate,
            'statusOptions' => $this->projectStatusModel->getActiveOptions(),
            'isFilteredUser' => $targetUserId !== null,
            'targetUser' => $targetUser,
        ];

        return view('projects/index', $data);
    }

    /**
     * @return array{start_date:string,end_date:string}|null
     */
    private function resolveProjectDateRange(string $startDate, string $endDate): ?array
    {
        $startDate = $this->isValidFilterDate($startDate) ? $startDate : '';
        $endDate = $this->isValidFilterDate($endDate) ? $endDate : '';

        if ($startDate === '' && $endDate === '') {
            return null;
        }

        if ($startDate === '') {
            $startDate = $endDate;
            $endDate = $this->getNextFilterDate($startDate);
        } elseif ($endDate === '') {
            $endDate = $this->getNextFilterDate($startDate);
        }

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function isValidFilterDate(string $date): bool
    {
        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsedDate !== false && $parsedDate->format('Y-m-d') === $date;
    }

    private function getNextFilterDate(string $date): string
    {
        return (new \DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');
    }

    private function getFormViewData(array $context): array
    {
        $project = $context['project'] ?? null;
        $selectedAssignedIds = $context['selectedAssignedIds'] ?? [];
        $selectedAssignedIds = array_values(array_filter(array_map('intval', (array) $selectedAssignedIds)));

        return array_merge($context, [
            'users' => $this->getAssignableUsers($selectedAssignedIds),
            'statusOptions' => $this->projectStatusModel->getActiveOptions(),
            'project' => $project,
            'selectedAssignedIds' => $selectedAssignedIds,
        ]);
    }

    private function projectRules(?int $ignoreId = null): array
    {
        $projectCodeRule = $ignoreId
            ? "required|max_length[50]|is_unique[projects.project_code,id,{$ignoreId}]"
            : 'required|max_length[50]|is_unique[projects.project_code]';

        $statusId = (int) $this->request->getPost('project_status_id');
        $isDeployment = false;
        if ($statusId > 0) {
            $statusRow = $this->projectStatusModel->find($statusId);
            $statusName = strtolower((string) ($statusRow['status_name'] ?? ''));
            $isDeployment = str_contains($statusName, 'deployment') || str_contains($statusName, 'complete') || str_contains($statusName, 'selesai') || str_contains($statusName, 'done');
        }

        $promoteDateRule = $isDeployment ? 'required|valid_date' : 'permit_empty|valid_date';

        return [
            'project_code' => [
                'rules' => $projectCodeRule,
                'errors' => [
                    'required' => 'Project Code wajib diisi.',
                    'is_unique' => 'Project Code sudah digunakan.',
                ],
            ],
            'name' => [
                'rules' => 'required|max_length[250]',
                'errors' => [
                    'required' => 'Nama Project wajib diisi.',
                ],
            ],
            'project_status_id' => [
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Status SDLC wajib dipilih.',
                ],
            ],
            'start_date' => [
                'rules' => 'required|valid_date',
                'errors' => [
                    'required' => 'Start Date wajib diisi.',
                    'valid_date' => 'Format Start Date tidak valid.',
                ],
            ],
            'end_date' => [
                'rules' => 'required|valid_date',
                'errors' => [
                    'required' => 'End Date wajib diisi.',
                    'valid_date' => 'Format End Date tidak valid.',
                ],
            ],
            'unit_testing_date' => 'permit_empty|valid_date',
            'sit_date' => 'permit_empty|valid_date',
            'uat_date' => 'permit_empty|valid_date',
            'promote_date' => [
                'rules' => $promoteDateRule,
                'errors' => [
                    'required' => 'Tanggal Promote wajib diisi ketika status project adalah Deployment.',
                    'valid_date' => 'Format Tanggal Promote tidak valid.',
                ],
            ],
        ];
    }

    private function buildProjectPayload(?array $existingProject = null): array
    {
        $selectedAssignedIds = $this->request->getPost('assigned_to');
        $selectedAssignedIds = is_array($selectedAssignedIds) ? $selectedAssignedIds : [];
        $selectedAssignedIds = array_values(array_filter(array_map('intval', $selectedAssignedIds), static fn (int $id) => $id > 0));

        $primaryUserId = $existingProject
            ? $this->getPrimaryAssignedUserId($existingProject['assigned_to'] ?? '')
            : (int) session()->get('user_id');

        if ($primaryUserId > 0) {
            $selectedAssignedIds = array_values(array_diff($selectedAssignedIds, [$primaryUserId]));
            array_unshift($selectedAssignedIds, $primaryUserId);
        }

        return [
            'project_code' => trim((string) $this->request->getPost('project_code')),
            'name' => trim((string) $this->request->getPost('name')),
            'notes' => $this->nullablePost('notes'),
            'project_status_id' => (int) $this->request->getPost('project_status_id'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'unit_testing_date' => $this->nullablePost('unit_testing_date'),
            'sit_date' => $this->nullablePost('sit_date'),
            'uat_date' => $this->nullablePost('uat_date'),
            'promote_date' => $this->nullablePost('promote_date'),
            'assigned_to' => implode(',', array_values(array_unique($selectedAssignedIds))),
        ];
    }

    private function validateUploadedFiles(): array
    {
        $errors = [];
        $files = $this->request->getFileMultiple('project_files');

        if (empty($files)) {
            return $errors;
        }

        foreach ($files as $file) {
            if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (!$file->isValid()) {
                $errors[] = 'Ada file project yang gagal diunggah.';
                continue;
            }

            if ($file->getSizeByUnit('mb') > 5) {
                $errors[] = 'Ukuran file project tidak boleh lebih dari 5 MB.';
            }

            $extension = strtolower((string) $file->getClientExtension());
            $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
            if (!in_array($extension, $allowedExtensions, true)) {
                $errors[] = 'Format file project harus PDF, Word, atau Excel.';
            }
        }

        return array_values(array_unique($errors));
    }

    private function storeUploadedFiles(int $projectId): void
    {
        $files = $this->request->getFileMultiple('project_files');

        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            if (!$file || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $binaryData = file_get_contents($file->getTempName());
            if ($binaryData === false) {
                continue;
            }

            $this->projectFileModel->insertUploadedFile([
                'project_id' => $projectId,
                'original_name' => $file->getClientName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'file_extension' => strtolower((string) $file->getClientExtension()),
                'file_size' => (int) $file->getSize(),
                'uploaded_by' => (int) session()->get('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            ], $binaryData);
        }
    }

    private function getPrimaryAssignedUserId(?string $assignedTo): int
    {
        $assignedIds = $this->parseAssignedToString($assignedTo);

        return $assignedIds[0] ?? (int) session()->get('user_id');
    }

    private function parseAssignedToString(?string $assignedTo): array
    {
        if (empty($assignedTo)) {
            return [];
        }

        $ids = array_map('trim', explode(',', $assignedTo));
        $ids = array_filter($ids, static fn ($id) => ctype_digit($id) && (int) $id > 0);

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function getAssignableUsers(array $selectedIds = []): array
    {
        $users = db_connect()
            ->table('users u')
            ->select('u.id, u.role_id, u.name, u.username, u.email, u.phone_number, u.job_title, u.is_active, r.role_name, r.category')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->orderBy('u.id', 'ASC')
            ->get()
            ->getResultArray();

        $selectedIds = array_values(array_filter(array_map('intval', $selectedIds)));

        return array_values(array_filter($users, static function (array $user) use ($selectedIds): bool {
            if ((int) ($user['is_active'] ?? 0) === 1) {
                return true;
            }

            return in_array((int) $user['id'], $selectedIds, true);
        }));
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }

    private function isKepalaDepartemen(): bool
    {
        return strtolower((string) session()->get('role_name')) === 'kepala departemen';
    }

    public function exportExcel()
    {
        $statusFilter = $this->request->getGet('status');
        $keyword = $this->request->getGet('keyword');
        $selectedStartDate = trim((string) $this->request->getGet('filter_start'));
        $selectedEndDate = trim((string) $this->request->getGet('filter_end'));
        $targetUserId = $this->request->getGet('user_id');
        $targetUserId = is_numeric($targetUserId) ? (int) $targetUserId : null;

        $dateRange = $this->resolveProjectDateRange($selectedStartDate, $selectedEndDate);
        $includeAll = $this->isKepalaDepartemen() && $targetUserId === null;
        $userId = $targetUserId ?? (int) session()->get('user_id');

        $projects = $this->projectModel->getAllProjectsWithAssignees($statusFilter, $keyword, $userId, $includeAll, $dateRange);

        helper('deadline');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Project');

        // Header Title
        $sheet->setCellValue('A1', 'LAPORAN PORTOFOLIO PROJECT TRACKER');
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E1E2D');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Metadata
        $statusObj = !empty($statusFilter) ? $this->projectStatusModel->find((int) $statusFilter) : null;
        $statusText = $statusObj['status_name'] ?? 'Semua Status';
        $periodText = (!empty($dateRange['start_date']) && !empty($dateRange['end_date']))
            ? date('d/m/Y', strtotime($dateRange['start_date'])) . ' s/d ' . date('d/m/Y', strtotime($dateRange['end_date']))
            : 'Semua Periode';

        $sheet->setCellValue('A2', 'Status SDLC: ' . $statusText . ' | Rentang Waktu: ' . $periodText . ' | Pencarian: ' . (!empty($keyword) ? $keyword : '-'));
        $sheet->mergeCells('A2:M2');
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('6C757D');

        $sheet->setCellValue('A3', 'Dicetak pada: ' . date('d M Y, H:i') . ' WIB | Dicetak oleh: ' . (session()->get('name') ?? 'User') . ' | Total: ' . count($projects) . ' Project');
        $sheet->mergeCells('A3:M3');
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('6C757D');

        // Column Headers
        $headers = [
            'A5' => 'No',
            'B5' => 'Kode Project',
            'C5' => 'Nama Project',
            'D5' => 'Status SDLC',
            'E5' => 'Deadline Status',
            'F5' => 'Assigned PIC',
            'G5' => 'Start Date',
            'H5' => 'End Date',
            'I5' => 'Unit Testing',
            'J5' => 'SIT',
            'K5' => 'UAT',
            'L5' => 'Promote Date',
            'M5' => 'Notes',
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
        $sheet->getStyle('A5:M5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Data Rows
        $row = 6;
        $no = 1;
        foreach ($projects as $prj) {
            $isCompleted = is_project_completed($prj);
            $deadline = get_deadline_status($prj['end_date'] ?? null, $isCompleted);

            $assignedNames = [];
            if (!empty($prj['assigned_users'])) {
                foreach ($prj['assigned_users'] as $u) {
                    $assignedNames[] = $u['name'] ?? '';
                }
            }

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $prj['project_code'] ?? '-');
            $sheet->setCellValue('C' . $row, $prj['name'] ?? '-');
            $sheet->setCellValue('D' . $row, $prj['status'] ?? '-');
            $sheet->setCellValue('E' . $row, $deadline['label'] ?? '-');
            $sheet->setCellValue('F' . $row, !empty($assignedNames) ? implode(', ', $assignedNames) : '-');
            $sheet->setCellValue('G' . $row, !empty($prj['start_date']) ? date('d/m/Y', strtotime($prj['start_date'])) : '-');
            $sheet->setCellValue('H' . $row, !empty($prj['end_date']) ? date('d/m/Y', strtotime($prj['end_date'])) : '-');
            $sheet->setCellValue('I' . $row, !empty($prj['unit_testing_date']) ? date('d/m/Y', strtotime($prj['unit_testing_date'])) : '-');
            $sheet->setCellValue('J' . $row, !empty($prj['sit_date']) ? date('d/m/Y', strtotime($prj['sit_date'])) : '-');
            $sheet->setCellValue('K' . $row, !empty($prj['uat_date']) ? date('d/m/Y', strtotime($prj['uat_date'])) : '-');
            $sheet->setCellValue('L' . $row, !empty($prj['promote_date']) ? date('d/m/Y', strtotime($prj['promote_date'])) : '-');
            $sheet->setCellValue('M' . $row, $prj['notes'] ?? '-');

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row . ':L' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

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
        $sheet->getStyle('A5:M' . $lastRow)->applyFromArray($borderStyle);

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Project_Tracker_' . date('Ymd_His') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportPdf()
    {
        $statusFilter = $this->request->getGet('status');
        $keyword = $this->request->getGet('keyword');
        $selectedStartDate = trim((string) $this->request->getGet('filter_start'));
        $selectedEndDate = trim((string) $this->request->getGet('filter_end'));
        $targetUserId = $this->request->getGet('user_id');
        $targetUserId = is_numeric($targetUserId) ? (int) $targetUserId : null;

        $dateRange = $this->resolveProjectDateRange($selectedStartDate, $selectedEndDate);
        $includeAll = $this->isKepalaDepartemen() && $targetUserId === null;
        $userId = $targetUserId ?? (int) session()->get('user_id');

        $projects = $this->projectModel->getAllProjectsWithAssignees($statusFilter, $keyword, $userId, $includeAll, $dateRange);

        helper('deadline');

        $statusObj = !empty($statusFilter) ? $this->projectStatusModel->find((int) $statusFilter) : null;
        $statusText = $statusObj['status_name'] ?? 'Semua Status';
        $periodText = (!empty($dateRange['start_date']) && !empty($dateRange['end_date']))
            ? date('d/m/Y', strtotime($dateRange['start_date'])) . ' s/d ' . date('d/m/Y', strtotime($dateRange['end_date']))
            : 'Semua Periode';

        $targetUser = $targetUserId ? $this->userModel->find($targetUserId) : null;
        $scopeText = $targetUser ? 'Proyek User: ' . ($targetUser['name'] ?? '') : ($includeAll ? 'Seluruh Proyek Departemen' : 'Proyek Ditugaskan');

        $html = view('projects/export_pdf', [
            'reportTitle' => 'Laporan Project Tracker',
            'projects' => $projects,
            'filterStatusLabel' => $statusText,
            'filterPeriodLabel' => $periodText,
            'userScopeLabel' => $scopeText,
            'keyword' => $keyword,
        ]);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Laporan_Project_Tracker_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    private function canAccessProject(int $projectId): bool
    {
        return $this->projectModel->getProjectDetail($projectId, (int) session()->get('user_id'), $this->isKepalaDepartemen()) !== null;
    }
}
