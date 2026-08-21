<?php
/**
 * @var array $user
 * @var array $assignedProjects
 */

$assignedProjects = $assignedProjects ?? [];

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

$statusBadge = static function (?string $status): string {
    return match ($status) {
        'Planning' => 'bg-secondary',
        'Defining' => 'bg-info',
        'Designing' => 'bg-primary',
        'Building' => 'bg-warning text-dark',
        'Testing' => 'bg-danger',
        'Deployment' => 'bg-success',
        default => 'bg-secondary',
    };
};

$dateValue = static fn ($value): string => !empty($value) ? date('d M Y', strtotime($value)) : '-';
$valueOrDash = static fn ($value): string => $value !== null && $value !== '' ? esc($value) : '-';
$isActive = (int) ($user['is_active'] ?? 0) === 1;
$isCurrentUser = (int) session()->get('user_id') === (int) ($user['id'] ?? 0);
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .user-detail-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
    }

    .user-project-actions {
        min-width: 7rem;
    }
</style>

<div class="page-heading d-flex justify-content-between align-items-start mb-3">
    <div>
        <a href="<?= base_url('/users') ?>" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Back to User Management
        </a>
        <h3><?= esc($user['name'] ?? 'Detail User') ?></h3>
        <p class="text-subtitle text-muted mb-0">@<?= esc($user['username'] ?? '-') ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <a href="<?= base_url('/users/edit/' . (int) $user['id']) ?>" class="btn btn-warning">
            <i class="bi bi-pencil-square me-1"></i> Edit User
        </a>
        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalResetPassword">
            <i class="bi bi-key-fill me-1"></i> Reset Password
        </button>
        <?php if ($isActive) : ?>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalDeactivateUser" <?= $isCurrentUser ? 'disabled' : '' ?>>
                <i class="bi bi-person-dash-fill me-1"></i> Deactivate User
            </button>
        <?php else : ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalActivateUser">
                <i class="bi bi-person-check-fill me-1"></i> Activate User
            </button>
        <?php endif; ?>
    </div>
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
    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="user-detail-avatar bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold fs-3">
                            <?= esc(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1"><?= esc($user['name'] ?? '-') ?></h5>
                            <span class="badge bg-light-<?= $roleClass($user['role_name'] ?? null) ?> text-<?= $roleClass($user['role_name'] ?? null) ?>">
                                <?= esc($user['role_name'] ?? '-') ?>
                            </span>
                            <span class="badge bg-light-<?= $categoryClass($user['category'] ?? null) ?> text-<?= $categoryClass($user['category'] ?? null) ?>">
                                <?= esc($user['category'] ?? '-') ?>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div>
                            <small class="text-muted d-block">Job Title</small>
                            <strong class="text-dark"><?= $valueOrDash($user['job_title'] ?? null) ?></strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Username</small>
                            <strong class="text-dark">@<?= esc($user['username'] ?? '-') ?></strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Email</small>
                            <strong class="text-dark"><?= $valueOrDash($user['email'] ?? null) ?></strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Nomor Telepon</small>
                            <strong class="text-dark"><?= $valueOrDash($user['phone_number'] ?? null) ?></strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Status Akun</small>
                            <?= $isActive ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non-Aktif</span>' ?>
                        </div>
                        <div>
                            <small class="text-muted d-block">Date Joined</small>
                            <strong class="text-dark"><?= $dateValue($user['created_at'] ?? null) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-kanban me-2 text-primary"></i>Assigned Projects</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Nama Proyek</th>
                                    <th>Status SDLC</th>
                                    <th>Deadline</th>
                                    <th class="text-center user-project-actions pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($assignedProjects)) : ?>
                                    <?php foreach ($assignedProjects as $project) : ?>
                                        <tr>
                                            <td class="ps-4">
                                                <strong class="text-dark d-block"><?= esc($project['name'] ?? '-') ?></strong>
                                                <span class="badge bg-light-secondary text-muted"><?= esc($project['project_code'] ?? '-') ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $statusBadge($project['status'] ?? null) ?>"><?= esc($project['status'] ?? '-') ?></span>
                                            </td>
                                            <td>
                                                <strong class="text-dark"><?= $dateValue($project['end_date'] ?? null) ?></strong>
                                            </td>
                                            <td class="text-center pe-4">
                                                <a href="<?= base_url('/projects/detail/' . (int) $project['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye-fill me-1"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Belum ada project yang ditugaskan ke user ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark"><i class="bi bi-key-fill me-2"></i>Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Password user <strong><?= esc($user['name'] ?? '-') ?></strong> akan direset ke password default sistem.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/users/reset-password/' . (int) $user['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-warning">Ya, Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDeactivateUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="bi bi-person-dash-fill me-2"></i>Deactivate User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">User <strong><?= esc($user['name'] ?? '-') ?></strong> akan dibuat nonaktif. Data historis tetap dipertahankan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/users/deactivate/' . (int) $user['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Deactivate User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalActivateUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bi bi-person-check-fill me-2"></i>Activate User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">User <strong><?= esc($user['name'] ?? '-') ?></strong> akan dibuat aktif kembali.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/users/activate/' . (int) $user['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success">Activate User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
