<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ProjectStatusModel;
use App\Models\UserModel;

class Projects extends BaseController
{
    protected $projectModel;
    protected $projectStatusModel;
    protected $userModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
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

    private function renderProjectList(?int $targetUserId = null)
    {
        $statusFilter = $this->request->getGet('status');
        $keyword      = $this->request->getGet('keyword');
        $includeAll   = $this->isKepalaDepartemen() && $targetUserId === null;
        $userId       = $targetUserId ?? (int) session()->get('user_id');
        $targetUser   = $targetUserId ? $this->userModel->find($targetUserId) : null;

        if ($targetUserId && !$targetUser) {
            return redirect()->to('/projects')->with('error', 'User tidak ditemukan.');
        }

        $data = [
            'title'          => 'Project Tracker',
            'projects'       => $this->projectModel->getProjectsWithAssignees($statusFilter, $keyword, $userId, $includeAll),
            'users'          => $this->userModel->where('is_active', 1)->findAll(),
            'selectedStatus' => $statusFilter,
            'keyword'        => $keyword,
            'statusOptions'  => $this->projectStatusModel->getActiveOptions(),
            'isFilteredUser' => $targetUserId !== null,
            'targetUser'     => $targetUser,
        ];

        return view('projects/index', $data);
    }

    public function detail($id)
    {
        $project = $this->projectModel->getProjectDetail($id, (int) session()->get('user_id'), $this->isKepalaDepartemen());

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $data = [
            'title'    => 'Detail Project - ' . $project['name'],
            'project'  => $project,
            'users'    => $this->userModel->where('is_active', 1)->findAll(),
            'statusOptions' => $this->projectStatusModel->getActiveOptions(),
        ];

        return view('projects/detail', $data);
    }

    public function store()
    {
        $rules = [
            'project_code' => 'required',
            'name'        => 'required|max_length[250]',
            'project_status_id' => 'required|is_natural_no_zero',
            'start_date'  => 'required|valid_date',
            'end_date'    => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $projectData = [
            'project_code'      => $this->request->getPost('project_code'),
            'name'              => $this->request->getPost('name'),
            'notes'             => $this->request->getPost('notes'),
            'project_status_id' => $this->request->getPost('project_status_id'),
            'start_date'        => $this->request->getPost('start_date'),
            'end_date'          => $this->request->getPost('end_date'),
            'unit_testing_date' => $this->request->getPost('unit_testing_date') ?: null,
            'sit_date'          => $this->request->getPost('sit_date') ?: null,
            'uat_date'          => $this->request->getPost('uat_date') ?: null,
            'promote_date'      => $this->request->getPost('promote_date') ?: null,
            'assigned_to'       => $this->buildAssignedToString($this->request->getPost('assigned_to')),
        ];

        $this->projectModel->insert($projectData);

        return redirect()->to('/projects')->with('success', 'Project berhasil ditambahkan!');
    }

    public function updateProgress($id)
    {
        if (!$this->canAccessProject((int) $id)) {
            return redirect()->to('/projects')->with('error', 'Akses ditolak. Anda tidak memiliki akses untuk memperbarui project ini.');
        }

        $projectStatusId = $this->request->getPost('project_status_id');
        $notes = $this->request->getPost('notes');

        $this->projectModel->update($id, [
            'project_status_id' => $projectStatusId,
            'notes' => $notes
        ]);

        return redirect()->back()->with('success', 'Progress project berhasil diperbarui!');
    }

    public function delete($id)
    {
        if (!$this->canAccessProject((int) $id)) {
            return redirect()->to('/projects')->with('error', 'Akses ditolak. Anda tidak memiliki akses untuk menghapus project ini.');
        }

        $this->projectModel->delete($id);

        return redirect()->to('/projects')->with('success', 'Project berhasil dihapus!');
    }

    private function buildAssignedToString($assignedTo): string
    {
        $currentUserId = (int) session()->get('user_id');
        $selectedUserIds = is_array($assignedTo) ? $assignedTo : [];

        $userIds = array_filter(array_map('intval', $selectedUserIds), static fn ($id) => $id > 0);

        if ($currentUserId > 0) {
            $userIds = array_values(array_diff($userIds, [$currentUserId]));
            array_unshift($userIds, $currentUserId);
        }

        return implode(',', array_unique($userIds));
    }

    private function isKepalaDepartemen(): bool
    {
        return strtolower((string) session()->get('role_name')) === 'kepala departemen';
    }

    private function canAccessProject(int $projectId): bool
    {
        return $this->projectModel->getProjectDetail($projectId, (int) session()->get('user_id'), $this->isKepalaDepartemen()) !== null;
    }
}
