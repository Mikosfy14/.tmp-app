<?php

namespace App\Controllers;

use App\Libraries\DashboardMetrics;
use App\Models\UserModel;

class Users extends BaseController
{
    private const DEFAULT_PASSWORD = 'user1234';

    protected UserModel $userModel;

    public function __construct()
    {
        helper(['form', 'deadline']);
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (!$this->isKepalaDepartemen()) {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        $keyword = trim((string) $this->request->getGet('keyword'));
        $roleFilter = (string) $this->request->getGet('role');
        $statusFilter = (string) $this->request->getGet('status');
        $pager = service('pager');
        $currentPage = $pager->getCurrentPage('users');
        $builder = db_connect()
            ->table('users u')
            ->select('u.id, u.role_id, u.name, u.username, u.email, u.phone_number, u.job_title, u.is_active, u.created_at, u.updated_at, r.role_name, r.category')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->orderBy('u.id', 'ASC');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('u.name', $keyword)
                ->orLike('u.username', $keyword)
                ->orLike('u.email', $keyword)
                ->orLike('u.phone_number', $keyword)
                ->orLike('u.job_title', $keyword)
                ->groupEnd();
        }

        if ($roleFilter !== '') {
            $builder->where('u.role_id', (int) $roleFilter);
        }

        if ($statusFilter !== '') {
            $builder->where('u.is_active', (int) $statusFilter);
        }

        $totalUsers = $builder->countAllResults(false);
        $pager->store('users', $currentPage, 10, $totalUsers);
        $users = $builder->get(10, ($currentPage - 1) * 10)->getResultArray();

        return view('users/index', [
            'title' => 'User Management',
            'users' => $users,
            'pager' => $pager,
            'roles' => $this->getRoles(),
            'userStats' => $this->getUserStats(),
            'selectedKeyword' => $keyword,
            'selectedRole' => $roleFilter,
            'selectedStatus' => $statusFilter,
        ]);
    }

    public function detail(int $id)
    {
        if (!$this->isKepalaDepartemen()) {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        $user = $this->getUserWithRole($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        $metrics = (new DashboardMetrics())->personal($id);

        return view('users/detail', [
            'title'            => 'Detail User - ' . $user['name'],
            'user'             => $user,
            'assignedProjects' => $metrics['projects'],
            'stats'            => $metrics['stats'],
            'sdlc_distribution' => $metrics['sdlc_distribution'],
            'completion_chart' => $metrics['chart'],
        ]);
    }

    public function create()
    {
        if (!$this->isKepalaDepartemen()) {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        return view('users/create', [
            'title' => 'Tambah User',
            'pageTitle' => 'Tambah User',
            'pageSubtitle' => 'Buat akun lokal dan tentukan role akses sesuai data role yang tersedia.',
            'roles' => $this->getRoles(),
            'formAction' => base_url('/users/store'),
            'submitLabel' => 'Simpan User',
            'cancelUrl' => base_url('/users'),
            'user' => null,
            'defaultPassword' => self::DEFAULT_PASSWORD,
        ]);
    }

    public function store()
    {
        if (!$this->isKepalaDepartemen()) {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        if (!$this->validate($this->userRules())) {
            return redirect()->to('/users/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->userModel->insert($this->buildUserPayload([
                'password_hash' => self::DEFAULT_PASSWORD,
            ]));
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menambahkan user: ' . $e->getMessage());

            return redirect()->to('/users/create')->withInput()->with('errors', ['Gagal menambahkan user. Pastikan data yang dimasukkan valid dan belum digunakan.']);
        }

        return redirect()->to('/users')->with('success', 'User berhasil ditambahkan dengan password default ' . self::DEFAULT_PASSWORD . '.');
    }

    public function edit(int $id)
    {
        if (!$this->isKepalaDepartemen()) {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        $user = $this->getUserWithRole($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        return view('users/edit', [
            'title' => 'Edit User - ' . $user['name'],
            'pageTitle' => 'Edit User',
            'pageSubtitle' => 'Perbarui profil, role akses, dan status akun user.',
            'roles' => $this->getRoles(),
            'formAction' => base_url('/users/update/' . $id),
            'submitLabel' => 'Simpan Perubahan',
            'cancelUrl' => base_url('/users'),
            'user' => $user,
            'defaultPassword' => self::DEFAULT_PASSWORD,
        ]);
    }

    public function update(int $id)
    {
        if (!$this->isKepalaDepartemen()) {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        if (!$this->getUserWithRole($id)) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        if (!$this->validate($this->userRules($id))) {
            return redirect()->to('/users/edit/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->userModel->update($id, $this->buildUserPayload());
        } catch (\Throwable $e) {
            log_message('error', 'Gagal memperbarui user: ' . $e->getMessage());

            return redirect()->to('/users/edit/' . $id)->withInput()->with('errors', ['Gagal memperbarui user. Pastikan data yang dimasukkan valid dan belum digunakan.']);
        }

        $this->syncCurrentUserSession($id);

        return redirect()->to('/users/detail/' . $id)->with('success', 'User berhasil diperbarui.');
    }

    public function resetPassword(int $id)
    {
        if (!$this->isKepalaDepartemen()) {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        if (!$this->getUserWithRole($id)) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        $this->userModel->update($id, ['password_hash' => self::DEFAULT_PASSWORD]);

        return redirect()->back()->with('success', 'Password user berhasil direset ke ' . self::DEFAULT_PASSWORD . '.');
    }

    public function activate(int $id)
    {
        return $this->setAccountStatus($id, 1);
    }

    public function deactivate(int $id)
    {
        if ((int) session()->get('user_id') === $id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menonaktifkan akun yang sedang digunakan.');
        }

        return $this->setAccountStatus($id, 0);
    }

    private function setAccountStatus(int $id, int $status)
    {
        if (!$this->isKepalaDepartemen()) {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        if (!$this->getUserWithRole($id)) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan.');
        }

        $this->userModel->update($id, ['is_active' => $status]);

        return redirect()->back()->with('success', $status === 1 ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.');
    }

    private function userRules(?int $ignoreId = null): array
    {
        $usernameRule = $ignoreId
            ? "required|max_length[100]|is_unique[users.username,id,{$ignoreId}]"
            : 'required|max_length[100]|is_unique[users.username]';

        $emailRule = $ignoreId
            ? "permit_empty|valid_email|max_length[150]|is_unique[users.email,id,{$ignoreId}]"
            : 'permit_empty|valid_email|max_length[150]|is_unique[users.email]';

        return [
            'name' => [
                'rules' => 'required|max_length[150]',
                'errors' => [
                    'required' => 'Nama lengkap wajib diisi.',
                ],
            ],
            'username' => [
                'rules' => $usernameRule,
                'errors' => [
                    'required' => 'Username wajib diisi.',
                    'is_unique' => 'Username sudah digunakan.',
                ],
            ],
            'email' => [
                'rules' => $emailRule,
                'errors' => [
                    'valid_email' => 'Format email tidak valid.',
                    'is_unique' => 'Email sudah digunakan.',
                ],
            ],
            'phone_number' => 'permit_empty|max_length[50]',
            'job_title' => 'permit_empty|max_length[150]',
            'role_id' => [
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Role wajib dipilih.',
                    'is_natural_no_zero' => 'Role tidak valid.',
                ],
            ],
            'is_active' => 'required|in_list[0,1]',
        ];
    }

    private function buildUserPayload(array $extra = []): array
    {
        return array_merge([
            'role_id' => (int) $this->request->getPost('role_id'),
            'name' => trim((string) $this->request->getPost('name')),
            'username' => trim((string) $this->request->getPost('username')),
            'email' => $this->nullablePost('email'),
            'phone_number' => $this->nullablePost('phone_number'),
            'job_title' => $this->nullablePost('job_title'),
            'is_active' => (int) $this->request->getPost('is_active'),
        ], $extra);
    }

    private function getUserWithRole(int $id): ?array
    {
        return db_connect()
            ->table('users u')
            ->select('u.id, u.role_id, u.name, u.username, u.email, u.phone_number, u.job_title, u.is_active, u.created_at, u.updated_at, r.role_name, r.category')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $id)
            ->get()
            ->getRowArray();
    }

    private function getAssignedProjects(int $userId): array
    {
        $cleanUserId = abs((int) $userId);
        if ($cleanUserId <= 0) {
            return [];
        }

        return db_connect()
            ->table('projects p')
            ->select('p.id, p.project_code, p.name, p.end_date, p.project_status_id, ps.status_name AS status')
            ->join('project_status ps', 'ps.id = p.project_status_id', 'left')
            ->where("CHARINDEX(',$cleanUserId,', ',' + ISNULL(p.assigned_to, '') + ',') >", 0, false)
            ->orderBy('p.end_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getRoles(): array
    {
        return db_connect()
            ->table('roles')
            ->select('id, role_name, category, description')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getUserStats(): array
    {
        $builder = db_connect()
            ->table('users u')
            ->select([
                'COUNT(u.id) AS total_users',
                'SUM(CASE WHEN u.is_active = 1 THEN 1 ELSE 0 END) AS active_users',
                'SUM(CASE WHEN r.category = \'Organik\' THEN 1 ELSE 0 END) AS organic_users',
                'SUM(CASE WHEN r.category = \'NonOrganik\' THEN 1 ELSE 0 END) AS non_organic_users',
            ], false)
            ->join('roles r', 'r.id = u.role_id', 'left');

        $stats = $builder->get()->getRowArray() ?: [];

        return [
            'totalUsers' => (int) ($stats['total_users'] ?? 0),
            'activeUsers' => (int) ($stats['active_users'] ?? 0),
            'organicUsers' => (int) ($stats['organic_users'] ?? 0),
            'nonOrganicUsers' => (int) ($stats['non_organic_users'] ?? 0),
        ];
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }

    private function syncCurrentUserSession(int $updatedUserId): void
    {
        if ((int) session()->get('user_id') !== $updatedUserId) {
            return;
        }

        $user = $this->getUserWithRole($updatedUserId);
        if (!$user) {
            return;
        }

        session()->set([
            'username' => $user['username'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'],
            'category' => $user['category'],
        ]);
    }

    private function isKepalaDepartemen(): bool
    {
        return strtolower((string) session()->get('role_name')) === 'kepala departemen';
    }
}
