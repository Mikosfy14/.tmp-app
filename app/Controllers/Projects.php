<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ProjectFileModel;
use App\Models\ProjectStatusModel;
use App\Models\DatabaseTypeModel;
use App\Models\UserModel;
use App\Models\ProjectLogbookModel;
use App\Models\NotificationModel;
use App\Services\ReportExportService;

class Projects extends BaseController
{
    protected ProjectModel $projectModel;
    protected ProjectFileModel $projectFileModel;
    protected ProjectStatusModel $projectStatusModel;
    protected DatabaseTypeModel $databaseTypeModel;
    protected UserModel $userModel;
    protected ProjectLogbookModel $projectLogbookModel;
    protected NotificationModel $notificationModel;
    protected ReportExportService $exportService;

    public function __construct()
    {
        helper(['form', 'deadline', 'notification', 'project_filter']);
        $this->projectModel = new ProjectModel();
        $this->projectFileModel = new ProjectFileModel();
        $this->projectStatusModel = new ProjectStatusModel();
        $this->databaseTypeModel = new DatabaseTypeModel();
        $this->userModel = new UserModel();
        $this->projectLogbookModel = new ProjectLogbookModel();
        $this->notificationModel = new NotificationModel();
        $this->exportService = new ReportExportService();
    }

    public function index()
    {
        return $this->renderProjectList();
    }

    public function user($userId)
    {
        if (!$this->isKepalaDepartemen()) {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        return $this->renderProjectList((int) $userId);
    }

    public function create()
    {
        return view('projects/create', $this->getFormViewData([
            'title' => 'Tambah Project - .tmp Workspace',
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
            'isKadept' => $this->isKepalaDepartemen(),
            'logbooks' => $this->projectLogbookModel->getLogbooksByProjectId((int) $project['id']),
            'allowedLogTypes' => $this->getAllowedLogTypes($project),
            'currentUserId' => (int) session()->get('user_id'),
        ]);
    }

    public function createLogbook($id)
    {
        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), $this->isKepalaDepartemen());

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $allowedLogTypes = $this->getAllowedLogTypes($project);
        if (empty($allowedLogTypes)) {
            return redirect()->to('/projects/detail/' . $project['id'])->with('error', 'Anda tidak memiliki hak untuk menambah Logbook pada project ini.');
        }

        $defaultType = in_array('team_log', $allowedLogTypes, true) ? 'team_log' : 'kadept_review';

        return view('projects/logbooks/form', [
            'title' => 'Tambah Log Mingguan - ' . $project['name'],
            'pageTitle' => 'Tambah Log Mingguan',
            'pageSubtitle' => 'Catat evaluasi mingguan atau capaian progres pengerjaan proyek.',
            'project' => $project,
            'statusOptions' => $this->projectStatusModel->findAll(),
            'allowedLogTypes' => $allowedLogTypes,
            'isEdit' => false,
            'log' => [
                'id' => null,
                'log_type' => old('log_type', $defaultType),
                'log_date' => date('Y-m-d'),
                'project_status_id' => $project['project_status_id'] ?? 3,
                'achievements' => old('achievements', ''),
                'blockers' => old('blockers', ''),
                'next_plans' => old('next_plans', ''),
                'kadept_notes' => '',
            ],
            'formAction' => base_url('/projects/' . $project['id'] . '/logbooks/store'),
        ]);
    }

    public function editLogbook($id, $logbookId)
    {
        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), $this->isKepalaDepartemen());

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // Load the requested persisted logbook.
        $log = $this->projectLogbookModel->getLogbookByProjectAndId((int) $project['id'], (int) $logbookId);
        $currentUserId = (int) session()->get('user_id');
        if (!$log || (int) $log['user_id'] !== $currentUserId || !in_array($log['log_type'], $this->getAllowedLogTypes($project), true)) {
            return redirect()->to('/projects/detail/' . $project['id'])->with('error', 'Logbook tidak ditemukan atau Anda tidak memiliki akses untuk mengubahnya.');
        }

        return view('projects/logbooks/form', [
            'title' => 'Edit Log Mingguan - ' . $project['name'],
            'pageTitle' => 'Edit Log Mingguan',
            'pageSubtitle' => 'Perbarui catatan evaluasi mingguan atau laporan progres teknis.',
            'project' => $project,
            'statusOptions' => $this->projectStatusModel->findAll(),
            'allowedLogTypes' => [$log['log_type']],
            'isEdit' => true,
            'log' => $log,
            'formAction' => base_url('/projects/' . $project['id'] . '/logbooks/' . $log['id'] . '/update'),
        ]);
    }

    public function storeLogbook($id)
    {
        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), $this->isKepalaDepartemen());
        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $logType = trim((string) $this->request->getPost('log_type'));
        if (!in_array($logType, $this->getAllowedLogTypes($project), true)) {
            return redirect()->to('/projects/' . $project['id'] . '/logbooks/create')->withInput()->with('error', 'Tipe Logbook tidak sesuai dengan hak akses Anda.');
        }

        $validationErrors = $this->validateLogbookInput();
        if (!empty($validationErrors)) {
            return redirect()->to('/projects/' . $project['id'] . '/logbooks/create')->withInput()->with('errors', $validationErrors);
        }

        $logbookId = $this->projectLogbookModel->insert($this->buildLogbookPayload($project, $logType));
        if (!$logbookId) {
            return redirect()->to('/projects/' . $project['id'] . '/logbooks/create')->withInput()->with('error', 'Logbook gagal disimpan.');
        }

        if ($logType === 'team_log') {
            $this->notificationModel->createLogbookSubmittedNotifications(
                (int) $project['id'],
                (int) $logbookId,
                (int) session()->get('user_id'),
                (string) $project['name']
            );
        }

        return redirect()->to('/projects/detail/' . $project['id'])->with('success', 'Logbook berhasil ditambahkan.');
    }

    public function updateLogbook($id, $logbookId)
    {
        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), $this->isKepalaDepartemen());
        $log = $this->projectLogbookModel->getLogbookByProjectAndId((int) $id, (int) $logbookId);
        $currentUserId = (int) session()->get('user_id');

        if (!$project || !$log || (int) $log['user_id'] !== $currentUserId) {
            return redirect()->to('/projects')->with('error', 'Logbook tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $postedType = trim((string) $this->request->getPost('log_type'));
        if ($postedType !== $log['log_type'] || !in_array($log['log_type'], $this->getAllowedLogTypes($project), true)) {
            return redirect()->to('/projects/' . $project['id'] . '/logbooks/' . $log['id'] . '/edit')->withInput()->with('error', 'Tipe Logbook tidak dapat diubah atau sudah tidak sesuai dengan hak akses Anda.');
        }

        $validationErrors = $this->validateLogbookInput();
        if (!empty($validationErrors)) {
            return redirect()->to('/projects/' . $project['id'] . '/logbooks/' . $log['id'] . '/edit')->withInput()->with('errors', $validationErrors);
        }

        $this->projectLogbookModel->update((int) $log['id'], $this->buildLogbookPayload($project, $log['log_type'], false));

        return redirect()->to('/projects/detail/' . $project['id'] . '#logbook-' . $log['id'])->with('success', 'Logbook berhasil diperbarui.');
    }

    public function deleteLogbook($id, $logbookId)
    {
        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), $this->isKepalaDepartemen());
        $log = $this->projectLogbookModel->getLogbookByProjectAndId((int) $id, (int) $logbookId);
        $currentUserId = (int) session()->get('user_id');

        if (!$project || !$log || (int) $log['user_id'] !== $currentUserId || !in_array($log['log_type'], $this->getAllowedLogTypes($project), true)) {
            return redirect()->to('/projects/detail/' . $id)->with('error', 'Logbook tidak ditemukan atau Anda tidak memiliki akses untuk menghapusnya.');
        }

        $this->projectLogbookModel->delete((int) $log['id']);

        return redirect()->to('/projects/detail/' . $project['id'])->with('success', 'Logbook berhasil dihapus.');
    }

    public function reviewLogbook($id, $logbookId)
    {
        if (!$this->isKepalaDepartemen()) {
            return redirect()->to('/projects')->with('error', 'Anda tidak memiliki akses untuk memberikan arahan.');
        }

        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), true);
        $log = $this->projectLogbookModel->getLogbookByProjectAndId((int) $id, (int) $logbookId);
        $notes = trim((string) $this->request->getPost('kadept_notes'));

        if (!$project || !$log) {
            return redirect()->to('/projects')->with('error', 'Logbook tidak ditemukan.');
        }

        if ($notes === '') {
            return redirect()->to('/projects/detail/' . $project['id'] . '#logbook-' . $log['id'])->with('error', 'Arahan Kepala Departemen wajib diisi.');
        }

        $sanitizedNotes = $this->sanitizeRichText($notes);
        $updated = $this->projectLogbookModel->update((int) $log['id'], [
            'kadept_notes' => $sanitizedNotes,
            'kadept_reviewed_by' => (int) session()->get('user_id'),
            'kadept_reviewed_at' => date('Y-m-d H:i:s'),
        ]);
        if (!$updated) {
            return redirect()->to('/projects/detail/' . $project['id'] . '#logbook-' . $log['id'])->with('error', 'Arahan Kepala Departemen gagal disimpan.');
        }

        $previousNotes = $this->sanitizeRichText((string) ($log['kadept_notes'] ?? ''));
        if ($sanitizedNotes !== $previousNotes && (int) $log['user_id'] !== (int) session()->get('user_id')) {
            $this->notificationModel->createGuidanceNotification(
                (int) $log['user_id'],
                (int) session()->get('user_id'),
                (int) $project['id'],
                (int) $log['id'],
                (string) $project['name'],
                sha1($sanitizedNotes),
                trim(strip_tags($sanitizedNotes))
            );
        }

        return redirect()->to('/projects/detail/' . $project['id'] . '#logbook-' . $log['id'])->with('success', 'Arahan Kepala Departemen berhasil disimpan.');
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

    public function bulkDownloadFiles()
    {
        $projectId = (int) $this->request->getPost('project_id');
        $fileIds = $this->request->getPost('file_ids');

        if (!is_array($fileIds) || empty($fileIds)) {
            return redirect()->back()->with('error', 'Tidak ada file yang dipilih untuk diunduh.');
        }

        $fileIds = array_values(array_filter(array_map('intval', $fileIds)));
        if (empty($fileIds)) {
            return redirect()->back()->with('error', 'Pilihan file tidak valid.');
        }

        if (!$this->canAccessProject($projectId)) {
            return redirect()->to('/projects')->with('error', 'Akses ditolak.');
        }

        $files = $this->projectFileModel->getFilesForDownloadByIds($fileIds);
        // Ensure all files belong to this project
        $files = array_values(array_filter($files, static fn ($f) => (int) ($f['project_id'] ?? 0) === $projectId));

        if (empty($files)) {
            return redirect()->back()->with('error', 'File yang dipilih tidak ditemukan.');
        }

        // Single file: download directly with original name
        if (count($files) === 1) {
            $file = $files[0];
            return $this->response
                ->setHeader('Content-Type', $file['mime_type'] ?? 'application/octet-stream')
                ->setHeader('Content-Disposition', 'attachment; filename="' . ($file['original_name'] ?? 'project-file') . '"')
                ->setBody($file['file_data']);
        }

        // Multiple files: pack into a zip archive
        $tempZipPath = tempnam(sys_get_temp_dir(), 'prj_zip_');
        $zip = new \ZipArchive();

        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file arsip zip.');
        }

        $usedNames = [];
        foreach ($files as $file) {
            $name = $file['original_name'] ?? 'file';
            // Prevent duplicate file names inside zip
            if (isset($usedNames[$name])) {
                $usedNames[$name]++;
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $base = pathinfo($name, PATHINFO_FILENAME);
                $name = $ext !== '' ? "{$base} ({$usedNames[$name]}).{$ext}" : "{$base} ({$usedNames[$name]})";
            } else {
                $usedNames[$name] = 1;
            }

            $zip->addFromString($name, $file['file_data']);
        }
        $zip->close();

        $zipData = file_get_contents($tempZipPath);
        @unlink($tempZipPath);

        $project = $this->projectModel->find($projectId);
        $projectCodeSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) ($project['project_code'] ?? 'PROJECT'));
        $zipFileName = 'Files_' . $projectCodeSafe . '_' . date('Ymd_His') . '.zip';

        return $this->response
            ->setHeader('Content-Type', 'application/zip')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $zipFileName . '"')
            ->setBody($zipData);
    }

    public function bulkDeleteFiles()
    {
        $projectId = (int) $this->request->getPost('project_id');
        $fileIds = $this->request->getPost('file_ids');

        if (!is_array($fileIds) || empty($fileIds)) {
            return redirect()->back()->with('error', 'Tidak ada file yang dipilih untuk dihapus.');
        }

        $fileIds = array_values(array_filter(array_map('intval', $fileIds)));
        if (empty($fileIds)) {
            return redirect()->back()->with('error', 'Pilihan file tidak valid.');
        }

        if (!$this->canAccessProject($projectId)) {
            return redirect()->to('/projects')->with('error', 'Akses ditolak.');
        }

        $files = $this->projectFileModel->getFilesForDownloadByIds($fileIds);
        $validIds = [];
        foreach ($files as $f) {
            if ((int) ($f['project_id'] ?? 0) === $projectId) {
                $validIds[] = (int) $f['id'];
            }
        }

        if (empty($validIds)) {
            return redirect()->back()->with('error', 'File yang dipilih tidak ditemukan.');
        }

        $this->projectFileModel->whereIn('id', $validIds)->delete();
        $count = count($validIds);

        return redirect()->to('/projects/edit/' . $projectId)->with('success', "{$count} file project berhasil dihapus.");
    }

    private function renderProjectList(?int $targetUserId = null)
    {
        $statusFilter = $this->request->getGet('status');
        $keyword = $this->request->getGet('keyword');
        $isCompletedFilter = $this->request->getGet('is_completed');
        $selectedStartDate = trim((string) $this->request->getGet('filter_start'));
        $selectedEndDate = trim((string) $this->request->getGet('filter_end'));
        $dateRange = $this->resolveProjectDateRange($selectedStartDate, $selectedEndDate);
        $selectedStartDate = $dateRange['start_date'] ?? '';
        $selectedEndDate = $dateRange['end_date'] ?? '';

        $isKadept = $this->isKepalaDepartemen();
        $currentUserId = (int) session()->get('user_id');
        $targetUser = $targetUserId ? $this->userModel->find($targetUserId) : null;

        if ($targetUserId && !$targetUser) {
            return redirect()->to('/projects')->with('error', 'User tidak ditemukan.');
        }

        $scope = 'my';
        $countMyProjects = 0;
        $countAllProjects = 0;

        if ($isKadept && $targetUserId === null) {
            $requestedScope = strtolower(trim((string) $this->request->getGet('scope')));
            $scope = ($requestedScope === 'all') ? 'all' : 'my';
            $includeAll = ($scope === 'all');
            $userId = $currentUserId;

            $countMyProjects = $this->projectModel->countProjects($currentUserId, false);
            $countAllProjects = $this->projectModel->countProjects(null, true);
        } else {
            $includeAll = false;
            $userId = $targetUserId ?? $currentUserId;
        }

        $data = [
            'title' => 'Project Tracker',
            'projects' => $this->projectModel->getProjectsWithAssignees($statusFilter, $keyword, $userId, $includeAll, $dateRange, $isCompletedFilter),
            'pager' => $this->projectModel->pager,
            'users' => $this->userModel->where('is_active', 1)->findAll(),
            'selectedStatus' => $statusFilter,
            'selectedIsCompleted' => $isCompletedFilter,
            'keyword' => $keyword,
            'selectedStartDate' => $selectedStartDate,
            'selectedEndDate' => $selectedEndDate,
            'statusOptions' => $this->projectStatusModel->getActiveOptions(),
            'isFilteredUser' => $targetUserId !== null,
            'targetUser' => $targetUser,
            'isKadept' => $isKadept,
            'scope' => $scope,
            'countMyProjects' => $countMyProjects,
            'countAllProjects' => $countAllProjects,
        ];

        return view('projects/index', $data);
    }

    /**
     * @return array{start_date:string,end_date:string}|null
     */
    private function resolveProjectDateRange(string $startDate, string $endDate): ?array
    {
        return resolve_project_date_range($startDate, $endDate);
    }

    private function getFormViewData(array $context): array
    {
        $project = $context['project'] ?? null;
        $selectedAssignedIds = $context['selectedAssignedIds'] ?? [];
        $selectedAssignedIds = array_values(array_filter(array_map('intval', (array) $selectedAssignedIds)));

        return array_merge($context, [
            'users' => $this->getAssignableUsers($selectedAssignedIds),
            'statusOptions' => $this->projectStatusModel->getActiveOptions(),
            'databaseTypeOptions' => $this->databaseTypeModel->getActiveOptions(),
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
            'database_type_id' => [
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Tipe Database wajib dipilih.',
                    'is_natural_no_zero' => 'Pilihan Tipe Database tidak valid.',
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
        $currentUserId = (int) session()->get('user_id');
        $isKadept = $this->isKepalaDepartemen();

        $primaryUserId = $existingProject
            ? $this->getPrimaryAssignedUserId($existingProject['assigned_to'] ?? '')
            : $currentUserId;

        // If editing an existing project and the current user is not the primary PIC nor Kepala Departemen:
        // preserve the existing assigned_to string completely so team members cannot tamper with assignees.
        if ($existingProject !== null && !$isKadept && $currentUserId !== $primaryUserId) {
            $assignedToString = (string) ($existingProject['assigned_to'] ?? '');
        } else {
            $selectedAssignedIds = $this->request->getPost('assigned_to');
            $selectedAssignedIds = is_array($selectedAssignedIds) ? $selectedAssignedIds : [];
            $selectedAssignedIds = array_values(array_filter(array_map('intval', $selectedAssignedIds), static fn (int $id) => $id > 0));

            if ($primaryUserId > 0) {
                $selectedAssignedIds = array_values(array_diff($selectedAssignedIds, [$primaryUserId]));
                array_unshift($selectedAssignedIds, $primaryUserId);
            }

            $assignedToString = implode(',', array_values(array_unique($selectedAssignedIds)));
        }

        return [
            'project_code' => trim((string) $this->request->getPost('project_code')),
            'name' => trim((string) $this->request->getPost('name')),
            'notes' => $this->nullablePost('notes'),
            'project_status_id' => (int) $this->request->getPost('project_status_id'),
            'database_type_id' => (int) $this->request->getPost('database_type_id'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'unit_testing_date' => $this->nullablePost('unit_testing_date'),
            'sit_date' => $this->nullablePost('sit_date'),
            'uat_date' => $this->nullablePost('uat_date'),
            'promote_date' => $this->nullablePost('promote_date'),
            'assigned_to' => $assignedToString,
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
        return strtolower((string) session()->get('role_name')) === 'kepala departemen'
            || (int) session()->get('role_id') === 1;
    }

    private function getAllowedLogTypes(array $project): array
    {
        $roleName = strtolower(trim((string) session()->get('role_name')));
        $roleId = (int) session()->get('role_id');
        $isKadept = $roleName === 'kepala departemen' || $roleId === 1;
        $userId = (int) session()->get('user_id');
        $assignedIds = $this->parseAssignedToString($project['assigned_to'] ?? '');
        $isAssignedPic = in_array($userId, $assignedIds, true);

        if ($roleId === 2 || $roleId === 3 || in_array($roleName, ['staff', 'manmonth'], true)) {
            return ['team_log'];
        }

        if (!$isKadept) {
            return [];
        }

        return $isAssignedPic ? ['team_log', 'kadept_review'] : ['kadept_review'];
    }

    private function validateLogbookInput(): array
    {
        $errors = [];
        $statusId = (int) $this->request->getPost('project_status_id');
        $logDate = trim((string) $this->request->getPost('log_date'));
        $achievements = trim((string) $this->request->getPost('achievements'));
        $nextPlans = trim((string) $this->request->getPost('next_plans'));

        if ($statusId <= 0 || !$this->projectStatusModel->find($statusId)) {
            $errors['project_status_id'] = 'Status SDLC yang dipilih tidak valid.';
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $logDate);
        if (!$date || $date->format('Y-m-d') !== $logDate) {
            $errors['log_date'] = 'Tanggal Logbook tidak valid.';
        }

        if ($achievements === '' || $this->isEmptyRichText($achievements)) {
            $errors['achievements'] = 'Capaian Minggu Ini wajib diisi.';
        } elseif (mb_strlen(strip_tags($achievements)) > 10000) {
            $errors['achievements'] = 'Capaian Minggu Ini terlalu panjang.';
        }

        if ($nextPlans === '' || $this->isEmptyRichText($nextPlans)) {
            $errors['next_plans'] = 'Rencana Minggu Depan wajib diisi.';
        } elseif (mb_strlen(strip_tags($nextPlans)) > 10000) {
            $errors['next_plans'] = 'Rencana Minggu Depan terlalu panjang.';
        }

        if (mb_strlen(strip_tags((string) $this->request->getPost('blockers'))) > 10000) {
            $errors['blockers'] = 'Kendala dan Masalah terlalu panjang.';
        }

        return $errors;
    }

    private function buildLogbookPayload(array $project, string $logType, bool $includeIdentity = true): array
    {
        $payload = [
            'project_status_id' => (int) ($project['project_status_id'] ?? 0),
            'log_type' => $logType,
            'log_date' => trim((string) $this->request->getPost('log_date')),
            'achievements' => $this->sanitizeRichText((string) $this->request->getPost('achievements')),
            'blockers' => $this->sanitizeRichText((string) $this->request->getPost('blockers')),
            'next_plans' => $this->sanitizeRichText((string) $this->request->getPost('next_plans')),
        ];

        if ($includeIdentity) {
            $payload['project_id'] = (int) $project['id'];
            $payload['user_id'] = (int) session()->get('user_id');
        }

        return $payload;
    }

    private function sanitizeRichText(string $value): string
    {
        return strip_tags(trim($value), '<p><br><strong><em><u><ol><ul><li>');
    }

    private function isEmptyRichText(string $value): bool
    {
        return trim(strip_tags($value)) === '';
    }

    public function exportExcel()
    {
        $statusFilter = $this->request->getGet('status');
        $isCompletedFilter = $this->request->getGet('is_completed');
        $keyword = $this->request->getGet('keyword');
        $selectedStartDate = trim((string) $this->request->getGet('filter_start'));
        $selectedEndDate = trim((string) $this->request->getGet('filter_end'));
        $targetUserId = $this->request->getGet('user_id');
        $targetUserId = is_numeric($targetUserId) ? (int) $targetUserId : null;
        $scope = strtolower(trim((string) $this->request->getGet('scope')));

        $dateRange = $this->resolveProjectDateRange($selectedStartDate, $selectedEndDate);
        $isKadept = $this->isKepalaDepartemen();
        $includeAll = $isKadept && $targetUserId === null && $scope === 'all';
        $userId = $targetUserId ?? (int) session()->get('user_id');

        $projects = $this->projectModel->getAllProjectsWithAssignees($statusFilter, $keyword, $userId, $includeAll, $dateRange, $isCompletedFilter);

        $statusObj = !empty($statusFilter) ? $this->projectStatusModel->find((int) $statusFilter) : null;
        $statusText = $statusObj['status_name'] ?? 'Semua Status';
        $periodText = (!empty($dateRange['start_date']) && !empty($dateRange['end_date']))
            ? date('d/m/Y', strtotime($dateRange['start_date'])) . ' s/d ' . date('d/m/Y', strtotime($dateRange['end_date']))
            : 'Semua Periode';
        $completionText = match ($isCompletedFilter) {
            '1', 'completed' => 'Completed',
            '0', 'not_completed' => 'Not Completed',
            default => 'Semua',
        };

        $targetUser = $targetUserId ? $this->userModel->find($targetUserId) : null;
        if ($targetUser) {
            $scopeText = 'Proyek User: ' . ($targetUser['name'] ?? '');
        } elseif ($includeAll) {
            $scopeText = 'Seluruh Proyek Tim Departemen';
        } else {
            $scopeText = $isKadept ? 'Proyek Saya (Kepala Departemen)' : 'Proyek Ditugaskan';
        }

        $metadataLines = [
            'Cakupan: ' . $scopeText . ' | Status SDLC: ' . $statusText . ' | Penyelesaian: ' . $completionText . ' | Rentang Waktu: ' . $periodText . ' | Pencarian: ' . (!empty($keyword) ? $keyword : '-'),
            'Dicetak pada: ' . \CodeIgniter\I18n\Time::now('Asia/Jakarta')->format('d M Y, H:i') . ' WIB | Dicetak oleh: ' . (session()->get('name') ?? 'User') . ' | Total: ' . count($projects) . ' Project',
        ];

        $headers = [
            'A' => 'No',
            'B' => 'Kode Project',
            'C' => 'Nama Project',
            'D' => 'Tipe Database',
            'E' => 'Status SDLC',
            'F' => 'Deadline Status',
            'G' => 'Assigned PIC',
            'H' => 'Start Date',
            'I' => 'End Date',
            'J' => 'Unit Testing',
            'K' => 'SIT',
            'L' => 'UAT',
            'M' => 'Promote Date',
            'N' => 'Notes',
        ];

        $rows = [];
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

            $rows[] = [
                'A' => $no++,
                'B' => $prj['project_code'] ?? '-',
                'C' => $prj['name'] ?? '-',
                'D' => $prj['database_type_name'] ?? '-',
                'E' => $prj['status'] ?? '-',
                'F' => $deadline['label'] ?? '-',
                'G' => !empty($assignedNames) ? implode(', ', $assignedNames) : '-',
                'H' => !empty($prj['start_date']) ? date('d/m/Y', strtotime($prj['start_date'])) : '-',
                'I' => !empty($prj['end_date']) ? date('d/m/Y', strtotime($prj['end_date'])) : '-',
                'J' => !empty($prj['unit_testing_date']) ? date('d/m/Y', strtotime($prj['unit_testing_date'])) : '-',
                'K' => !empty($prj['sit_date']) ? date('d/m/Y', strtotime($prj['sit_date'])) : '-',
                'L' => !empty($prj['uat_date']) ? date('d/m/Y', strtotime($prj['uat_date'])) : '-',
                'M' => !empty($prj['promote_date']) ? date('d/m/Y', strtotime($prj['promote_date'])) : '-',
                'N' => $prj['notes'] ?? '-',
            ];
        }

        $centerColumns = ['A', 'B', 'D', 'E', 'F', 'H', 'I', 'J', 'K', 'L', 'M'];
        $filename = 'Laporan_Project_Tracker_' . date('Ymd_His') . '.xlsx';

        $this->exportService->exportExcel(
            $filename,
            'Data Project',
            'LAPORAN PORTOFOLIO PROJECT TRACKER',
            $metadataLines,
            $headers,
            $rows,
            $centerColumns
        );
    }

    public function exportPdf()
    {
        $statusFilter = $this->request->getGet('status');
        $isCompletedFilter = $this->request->getGet('is_completed');
        $keyword = $this->request->getGet('keyword');
        $selectedStartDate = trim((string) $this->request->getGet('filter_start'));
        $selectedEndDate = trim((string) $this->request->getGet('filter_end'));
        $targetUserId = $this->request->getGet('user_id');
        $targetUserId = is_numeric($targetUserId) ? (int) $targetUserId : null;
        $scope = strtolower(trim((string) $this->request->getGet('scope')));

        $dateRange = $this->resolveProjectDateRange($selectedStartDate, $selectedEndDate);
        $isKadept = $this->isKepalaDepartemen();
        $includeAll = $isKadept && $targetUserId === null && $scope === 'all';
        $userId = $targetUserId ?? (int) session()->get('user_id');

        $projects = $this->projectModel->getAllProjectsWithAssignees($statusFilter, $keyword, $userId, $includeAll, $dateRange, $isCompletedFilter);

        $statusObj = !empty($statusFilter) ? $this->projectStatusModel->find((int) $statusFilter) : null;
        $statusText = $statusObj['status_name'] ?? 'Semua Status';
        $periodText = (!empty($dateRange['start_date']) && !empty($dateRange['end_date']))
            ? date('d/m/Y', strtotime($dateRange['start_date'])) . ' s/d ' . date('d/m/Y', strtotime($dateRange['end_date']))
            : 'Semua Periode';
        $completionText = match ($isCompletedFilter) {
            '1', 'completed' => 'Completed',
            '0', 'not_completed' => 'Not Completed',
            default => 'Semua',
        };

        $targetUser = $targetUserId ? $this->userModel->find($targetUserId) : null;
        if ($targetUser) {
            $scopeText = 'Proyek User: ' . ($targetUser['name'] ?? '');
        } elseif ($includeAll) {
            $scopeText = 'Seluruh Proyek Tim Departemen';
        } else {
            $scopeText = $isKadept ? 'Proyek Saya (Kepala Departemen)' : 'Proyek Ditugaskan';
        }

        $filename = 'Laporan_Project_Tracker_' . date('Ymd_His') . '.pdf';

        $this->exportService->exportPdf($filename, 'projects/export_pdf', [
            'reportTitle' => 'Laporan Project Tracker',
            'projects' => $projects,
            'filterStatusLabel' => $statusText,
            'filterPeriodLabel' => $periodText,
            'filterCompletionLabel' => $completionText,
            'userScopeLabel' => $scopeText,
            'keyword' => $keyword,
        ]);
    }

    private function canAccessProject(int $projectId): bool
    {
        return $this->projectModel->getProjectDetail($projectId, (int) session()->get('user_id'), $this->isKepalaDepartemen()) !== null;
    }
}
