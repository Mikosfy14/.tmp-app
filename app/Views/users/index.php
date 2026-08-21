<?php
/**
 * @var array $users
 * @var array $roles
 * @var array $userStats
 * @var \CodeIgniter\Pager\Pager|null $pager
 */

$users = $users ?? [];
$roles = $roles ?? [];
$userStats = $userStats ?? [];

$roleClass = static function (?string $roleName): string {
    return match ($roleName) {
        'Kepala Departemen' => 'primary',
        'Staff' => 'success',
        'Manmonth' => 'warning',
        default => 'secondary',
    };
};

$categoryClass = static function (?string $category): string {
    return match ($category) {
        'Organik' => 'info',
        'NonOrganik' => 'warning',
        default => 'secondary',
    };
};

$valueOrDash = static fn ($value): string => $value !== null && $value !== '' ? esc($value) : '-';

$totalUsers = (int) ($userStats['totalUsers'] ?? 0);
$activeUsers = (int) ($userStats['activeUsers'] ?? 0);
$inactiveUsers = max(0, $totalUsers - $activeUsers);
$organicUsers = (int) ($userStats['organicUsers'] ?? 0);
$nonOrganicUsers = (int) ($userStats['nonOrganicUsers'] ?? 0);
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .user-management-actions {
        min-width: 13.5rem;
        white-space: nowrap;
    }

    .user-management-action-group {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .375rem;
        flex-wrap: nowrap;
    }

    .user-management-action-group .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .25rem;
        min-width: 4.25rem;
    }

    .user-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
    }

    .user-pagination .pagination {
        margin: 0;
        gap: .35rem;
    }

    .user-pagination .page-item .page-link {
        border: 0;
        border-radius: .55rem;
        min-width: 2.25rem;
        text-align: center;
        color: #52606d;
        font-weight: 600;
    }

    .user-pagination .page-item.active .page-link {
        background: #435ebe;
        color: #fff;
        box-shadow: 0 .25rem .65rem rgba(67, 94, 190, .25);
    }

    .user-pagination .page-item:not(.active) .page-link:hover {
        background: #eef1ff;
        color: #435ebe;
    }

    .user-pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background: #f1f3f5;
        opacity: .75;
        cursor: not-allowed;
        pointer-events: none;
    }
</style>

<div class="page-heading d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3>User Management</h3>
        <p class="text-subtitle text-muted mb-0">Kelola akun lokal, role, dan status aktif pengguna.</p>
    </div>
    <a href="<?= base_url('/users/create') ?>" class="btn btn-primary shadow-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah User
    </a>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="page-content">
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body py-3">
                    <span class="text-muted text-sm fw-semibold">Total User</span>
                    <h3 class="fw-bold mb-0 text-primary"><?= $totalUsers ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body py-3">
                    <span class="text-muted text-sm fw-semibold">User Aktif</span>
                    <h3 class="fw-bold mb-0 text-success"><?= $activeUsers ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body py-3">
                    <span class="text-muted text-sm fw-semibold">Organik</span>
                    <h3 class="fw-bold mb-0 text-info"><?= $organicUsers ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body py-3">
                    <span class="text-muted text-sm fw-semibold">NonOrganik</span>
                    <h3 class="fw-bold mb-0 text-warning"><?= $nonOrganicUsers ?></h3>
                    <?php if ($inactiveUsers > 0) : ?>
                        <small class="text-muted"><?= $inactiveUsers ?> user nonaktif</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="get" action="<?= base_url('/users') ?>" class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                        <input type="text" name="keyword" class="form-control" placeholder="Cari nama, username, email, jabatan..." value="<?= esc($selectedKeyword ?? '') ?>">
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <select name="role" class="form-select">
                        <option value="">Semua Role</option>
                        <?php foreach ($roles as $role) : ?>
                            <option value="<?= esc($role['id']) ?>" <?= (string) ($selectedRole ?? '') === (string) $role['id'] ? 'selected' : '' ?>><?= esc($role['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1" <?= ($selectedStatus ?? '') === '1' ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= ($selectedStatus ?? '') === '0' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12 col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
                <div class="col-12 col-lg-1">
                    <a href="<?= base_url('/users') ?>" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Role</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-center user-management-actions pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)) : ?>
                        <?php foreach ($users as $user) : ?>
                            <?php
                            $roleColor = $roleClass($user['role_name'] ?? null);
                            $categoryColor = $categoryClass($user['category'] ?? null);
                            $isActive = (int) ($user['is_active'] ?? 0) === 1;
                            $isKepalaDepartemenUser = strtolower((string) ($user['role_name'] ?? '')) === 'kepala departemen';
                            $searchText = strtolower(implode(' ', array_filter([
                                $user['name'] ?? '',
                                $user['username'] ?? '',
                                $user['email'] ?? '',
                                $user['phone_number'] ?? '',
                                $user['job_title'] ?? '',
                                $user['role_name'] ?? '',
                                $user['category'] ?? '',
                            ])));
                            ?>
                            <tr class="user-row"
                                data-search="<?= esc($searchText) ?>"
                                data-role="<?= esc($user['role_name'] ?? '') ?>"
                                data-category="<?= esc($user['category'] ?? '') ?>"
                                data-status="<?= $isActive ? '1' : '0' ?>">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold">
                                            <?= esc(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?>
                                        </div>
                                        <div>
                                            <strong class="d-block text-dark"><?= esc($user['name']) ?></strong>
                                            <small class="text-muted">@<?= esc($user['username']) ?></small>
                                            <small class="d-block text-muted"><?= $valueOrDash($user['job_title'] ?? null) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light-<?= $roleColor ?> text-<?= $roleColor ?>"><?= esc($user['role_name'] ?? '-') ?></span>
                                    <span class="badge bg-light-<?= $categoryColor ?> text-<?= $categoryColor ?> d-block mt-1" style="width: fit-content;"><?= esc($user['category'] ?? '-') ?></span>
                                </td>
                                <td class="py-3">
                                    <span class="fw-semibold text-dark"><?= $valueOrDash($user['email'] ?? null) ?></span>
                                    <small class="d-block text-muted"><?= $valueOrDash($user['phone_number'] ?? null) ?></small>
                                </td>
                                <td class="py-3">
                                    <?php if ($isActive) : ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i>Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <small class="fw-semibold text-dark"><?= !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-' ?></small>
                                    <small class="d-block text-muted">Updated: <?= !empty($user['updated_at']) ? date('d M Y', strtotime($user['updated_at'])) : '-' ?></small>
                                </td>
                                <td class="text-center user-management-actions pe-4 py-3">
                                    <div class="user-management-action-group">
                                        <a href="<?= base_url('/users/detail/' . (int) $user['id']) ?>" class="btn btn-sm btn-outline-primary" title="Detail User">
                                            <i class="bi bi-eye-fill"></i> Detail
                                        </a>
                                        <a href="<?= base_url('/users/edit/' . (int) $user['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit User">
                                                <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <?php if (!$isKepalaDepartemenUser || (int) ($user['id'] ?? 0) !== (int) session()->get('user_id')) : ?>
                                            <?php if ($isActive) : ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeactivateUser<?= esc($user['id']) ?>" title="Nonaktifkan User">
                                                    <i class="bi bi-person-dash-fill"></i> Nonaktif
                                                </button>
                                            <?php else : ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalActivateUser<?= esc($user['id']) ?>" title="Aktifkan User">
                                                    <i class="bi bi-person-check-fill"></i> Aktif
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else : ?>
                        <tr id="usersEmptyRow">
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada user yang bisa ditampilkan.</td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($users)) : ?>
                        <tr id="usersEmptyRow" class="d-none">
                            <td colspan="6" class="text-center py-4 text-muted">Data user tidak ditemukan.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($users) && !empty($pager) && $pager->getPageCount('users') > 1) : ?>
                <div class="user-pagination d-flex justify-content-end p-3 border-top">
                    <?= $pager->links('users', 'complete') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php foreach ($users as $user) : ?>
    <?php
    $roleColor = $roleClass($user['role_name'] ?? null);
    $categoryColor = $categoryClass($user['category'] ?? null);
    $isActive = (int) ($user['is_active'] ?? 0) === 1;
    $isKepalaDepartemenUser = strtolower((string) ($user['role_name'] ?? '')) === 'kepala departemen';
    ?>
    <div class="modal fade" id="modalUserDetail<?= esc($user['id']) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-person-vcard me-2"></i>Detail User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="user-avatar bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold">
                            <?= esc(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1"><?= esc($user['name']) ?></h5>
                            <small class="text-muted">@<?= esc($user['username']) ?></small>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Role</small>
                            <span class="badge bg-light-<?= $roleColor ?> text-<?= $roleColor ?>"><?= esc($user['role_name'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Kategori</small>
                            <span class="badge bg-light-<?= $categoryColor ?> text-<?= $categoryColor ?>"><?= esc($user['category'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Email</small>
                            <strong class="text-dark"><?= $valueOrDash($user['email'] ?? null) ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Phone Number</small>
                            <strong class="text-dark"><?= $valueOrDash($user['phone_number'] ?? null) ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Job Title</small>
                            <strong class="text-dark"><?= $valueOrDash($user['job_title'] ?? null) ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Status</small>
                            <?= $isActive ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Created At</small>
                            <strong class="text-dark"><?= !empty($user['created_at']) ? date('d M Y H:i', strtotime($user['created_at'])) : '-' ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Updated At</small>
                            <strong class="text-dark"><?= !empty($user['updated_at']) ? date('d M Y H:i', strtotime($user['updated_at'])) : '-' ?></strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <?php if (!$isKepalaDepartemenUser) : ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditUser<?= esc($user['id']) ?>"><i class="bi bi-pencil-square me-1"></i> Edit User</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditUser<?= esc($user['id']) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil-square me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" class="form-control" value="<?= esc($user['name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Username</label>
                            <input type="text" class="form-control" value="<?= esc($user['username']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" value="<?= esc($user['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" class="form-control" value="<?= esc($user['phone_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Job Title</label>
                            <input type="text" class="form-control" value="<?= esc($user['job_title'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role</label>
                            <select class="form-select">
                                <?php foreach ($roles as $role) : ?>
                                    <option value="<?= esc($role['id']) ?>" <?= (int) ($user['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : '' ?>>
                                        <?= esc($role['role_name']) ?> - <?= esc($role['category']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password Baru</label>
                            <input type="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select">
                                <option value="1" <?= $isActive ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= !$isActive ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal"><i class="bi bi-check-circle me-1"></i> Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDeactivateUser<?= esc($user['id']) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-person-dash-fill me-2"></i>Nonaktifkan User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">User <strong><?= esc($user['name']) ?></strong> akan dibuat nonaktif. Data historis tetap dipertahankan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="<?= base_url('/users/deactivate/' . (int) $user['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger">Nonaktifkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalActivateUser<?= esc($user['id']) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-person-check-fill me-2"></i>Aktifkan User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">User <strong><?= esc($user['name']) ?></strong> akan dibuat aktif kembali.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="<?= base_url('/users/activate/' . (int) $user['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success">Aktifkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="modal fade" id="modalUserForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bi bi-person-plus-fill me-2"></i>Form User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" class="form-control" placeholder="Nama user">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" placeholder="username.login">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" placeholder="user@example.local">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Job Title</label>
                        <input type="text" class="form-control" placeholder="Backend Developer">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Role</label>
                        <select class="form-select">
                            <?php foreach ($roles as $role) : ?>
                                <option value="<?= esc($role['id']) ?>"><?= esc($role['role_name']) ?> - <?= esc($role['category']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control" placeholder="Password awal">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <select class="form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="bi bi-save me-1"></i> Simpan User</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
