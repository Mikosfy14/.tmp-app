<?php

/**
 * @var array $user
 * @var array $assignedProjects
 * @var array $stats
 * @var array $sdlc_distribution
 * @var array $completion_chart
 */

$assignedProjects = $assignedProjects ?? [];
$stats = $stats ?? [];
$sdlc_distribution = $sdlc_distribution ?? [];
$completion_chart = $completion_chart ?? [];

helper('navigation');
$backNav = get_contextual_back('/users', 'Kembali ke Kelola Pengguna');

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
        'Planning' => 'bg-secondary text-white',
        'Defining' => 'bg-info text-dark',
        'Designing' => 'bg-primary text-white',
        'Building' => 'bg-warning text-dark',
        'Testing' => 'bg-danger text-white',
        'Deployment' => 'bg-success text-white',
        default => 'bg-secondary text-white',
    };
};

$dateValue = static fn($value): string => !empty($value) ? date('d M Y', strtotime($value)) : '-';
$valueOrDash = static fn($value): string => $value !== null && $value !== '' ? esc($value) : '-';
$isActive = (int) ($user['is_active'] ?? 0) === 1;
$isCurrentUser = (int) session()->get('user_id') === (int) ($user['id'] ?? 0);
$isProtectedUser = (int) ($user['role_id'] ?? 0) === 1 || strtolower((string) ($user['role_name'] ?? '')) === 'kepala departemen' || $isCurrentUser;

// KPI Calculations
$totalCompleted = (int) ($stats['total_completed'] ?? 0);
$onTimeDone = (int) ($stats['on_time_done'] ?? 0);
$lateDone = (int) ($stats['late_done'] ?? 0);
$activeProjects = (int) ($stats['active_projects'] ?? 0);
$overdue = (int) ($stats['overdue'] ?? 0);
$riskUrgent = (int) ($stats['risk_urgent'] ?? 0);
$totalApps = (int) ($stats['total_apps_managed'] ?? 0);

$totalAll = $totalCompleted + $activeProjects;
$completionRate = $totalAll > 0 ? round(($totalCompleted / $totalAll) * 100, 1) : 0;
$onTimeRate = $totalCompleted > 0 ? round(($onTimeDone / $totalCompleted) * 100, 1) : 0;

// Separate active vs completed projects for tabs
$activeProjectsList = [];
$completedProjectsList = [];
foreach ($assignedProjects as $p) {
    if (is_project_completed($p)) {
        $completedProjectsList[] = $p;
    } else {
        $activeProjectsList[] = $p;
    }
}
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .user-detail-header {
        background-color: var(--bs-card-bg, #ffffff);
        border: 1px solid var(--bs-border-color, #e9ecef);
        border-radius: 12px;
        padding: 1.5rem;
    }

    [data-bs-theme="dark"] .user-detail-header {
        background-color: #1e1e2d !important;
        border-color: #2b2b40 !important;
    }

    [data-bs-theme="dark"] .user-detail-header h3,
    [data-bs-theme="dark"] .user-detail-header .meta-item-value {
        color: #ffffff !important;
    }

    [data-bs-theme="dark"] .user-detail-header .border-top {
        border-color: #2b2b40 !important;
    }

    .avatar-initial-lg {
        width: 58px;
        height: 58px;
        border-radius: 12px;
        background-color: rgba(67, 94, 190, 0.12);
        color: #435ebe;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    [data-bs-theme="dark"] .avatar-initial-lg {
        background-color: rgba(143, 160, 240, 0.18);
        color: #8fa0f0;
    }

    .meta-item-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    [data-bs-theme="dark"] .meta-item-label {
        color: #a0aec0;
    }

    .meta-item-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--bs-body-color);
    }

    .kpi-card {
        border: 1px solid var(--bs-border-color, #e9ecef);
        border-radius: 10px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .kpi-card .kpi-value {
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .kpi-card .kpi-sub {
        font-size: 0.75rem;
    }

    [data-bs-theme="dark"] .kpi-card {
        background-color: #1e1e2d !important;
        border-color: #2b2b40 !important;
    }

    .user-project-actions {
        min-width: 6.5rem;
    }

    .user-project-code {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    @media (max-width: 767.98px) {
        .user-detail-header {
            padding: 1.25rem 1rem;
        }

        .user-header-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            width: 100%;
        }

        .user-header-actions .btn-edit-user {
            grid-column: span 2;
        }

        .user-header-actions .btn {
            width: 100%;
            text-align: center;
            justify-content: center;
        }

        .user-project-mobile-card {
            border: 1px solid var(--bs-border-color, #e9ecef);
            border-radius: 10px;
            padding: 1rem;
            background-color: var(--bs-card-bg, #ffffff);
            margin-bottom: 0.75rem;
        }

        [data-bs-theme="dark"] .user-project-mobile-card {
            background-color: #252539 !important;
            border-color: #2b2b40 !important;
        }

        .user-project-mobile-card:last-child {
            margin-bottom: 0;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- Navigation Back Link -->
<div class="mb-3">
    <a href="<?= esc($backNav['url'], 'attr') ?>" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> <?= esc($backNav['label']) ?>
    </a>
</div>

<!-- Feedback Flash Alerts -->
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Executive Header Card (Zero Redundancy) -->
<div class="card user-detail-header shadow-sm mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-initial-lg">
                <?= esc(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?>
            </div>
            <div>
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <span class="badge bg-light-<?= $roleClass($user['role_name'] ?? null) ?> text-<?= $roleClass($user['role_name'] ?? null) ?> border border-<?= $roleClass($user['role_name'] ?? null) ?>-subtle fw-semibold">
                        <?= esc($user['role_name'] ?? '-') ?>
                    </span>
                    <span class="badge bg-light-<?= $categoryClass($user['category'] ?? null) ?> text-<?= $categoryClass($user['category'] ?? null) ?> border border-<?= $categoryClass($user['category'] ?? null) ?>-subtle fw-semibold">
                        <?= esc($user['category'] ?? '-') ?>
                    </span>
                    <?php if ($isActive) : ?>
                        <span class="badge bg-light-success text-success border border-success-subtle fw-semibold">Aktif</span>
                    <?php else : ?>
                        <span class="badge bg-light-secondary text-secondary border border-secondary-subtle fw-semibold">Non-Aktif</span>
                    <?php endif; ?>
                </div>
                <h3 class="h4 fw-bold mb-1 text-body">
                    <?= esc($user['name'] ?? '-') ?>
                </h3>
                <div class="text-muted small">
                    <span class="fw-semibold">@<?= esc($user['username'] ?? '-') ?></span>
                    <?php if (!empty($user['job_title'])) : ?>
                        <span class="mx-1">&middot;</span>
                        <span><?= esc($user['job_title']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 align-self-stretch align-self-md-auto user-header-actions">
            <?php if ($isProtectedUser) : ?>
                <a href="<?= base_url('/profile/edit') ?>" class="btn btn-sm btn-outline-primary px-3 py-2 fw-semibold">
                    <i class="bi bi-pencil-square me-1"></i> Edit Profil
                </a>
            <?php else : ?>
                <a href="<?= base_url('/users/edit/' . (int) $user['id']) ?>" class="btn btn-sm btn-outline-primary px-3 py-2 fw-semibold btn-edit-user">
                    Edit Pengguna
                </a>
                <button type="button" class="btn btn-sm btn-outline-warning px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalResetPassword">
                    Reset Password
                </button>
                <?php if ($isActive) : ?>
                    <button type="button" class="btn btn-sm btn-outline-danger px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalDeactivateUser">
                        Nonaktifkan
                    </button>
                <?php else : ?>
                    <button type="button" class="btn btn-sm btn-outline-success px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalActivateUser">
                        Aktifkan
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Integrated Meta Row (Zero Redundancy) -->
    <div class="row g-3 pt-4 mt-3 border-top">
        <div class="col-6 col-md-3">
            <div class="meta-item-label">Email</div>
            <div class="meta-item-value text-break"><?= $valueOrDash($user['email'] ?? null) ?></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="meta-item-label">Nomor Telepon</div>
            <div class="meta-item-value"><?= $valueOrDash($user['phone_number'] ?? null) ?></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="meta-item-label">Tanggal Bergabung</div>
            <div class="meta-item-value"><?= $dateValue($user['created_at'] ?? null) ?></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="meta-item-label">Aplikasi Dikelola</div>
            <div class="meta-item-value"><?= $totalApps ?> Aplikasi</div>
        </div>
    </div>
</div>

<div class="page-content">
    <!-- Row 1: KPI Metrics Grid -->
    <div class="row g-3 mb-4">
        <!-- Completion Rate -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 kpi-card">
                <div class="card-body p-3 d-flex flex-column justify-content-center">
                    <div class="meta-item-label mb-1">Completion Rate</div>
                    <span class="kpi-value <?= $completionRate >= 70 ? 'text-success' : ($completionRate >= 40 ? 'text-warning' : 'text-danger') ?>">
                        <?= $completionRate ?>%
                    </span>
                    <small class="text-muted kpi-sub mt-1">
                        <?= $totalCompleted ?> / <?= $totalAll ?> proyek selesai
                    </small>
                </div>
            </div>
        </div>

        <!-- On-Time Rate -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 kpi-card">
                <div class="card-body p-3 d-flex flex-column justify-content-center">
                    <div class="meta-item-label mb-1">On-Time Rate</div>
                    <span class="kpi-value <?= $onTimeRate >= 70 ? 'text-success' : ($onTimeRate >= 40 ? 'text-warning' : 'text-danger') ?>">
                        <?= $onTimeRate ?>%
                    </span>
                    <small class="text-muted kpi-sub mt-1">
                        <?= $onTimeDone ?> tepat waktu &middot; <?= $lateDone ?> terlambat
                    </small>
                </div>
            </div>
        </div>

        <!-- Active Projects -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 kpi-card">
                <div class="card-body p-3 d-flex flex-column justify-content-center">
                    <div class="meta-item-label mb-1">Proyek Aktif</div>
                    <span class="kpi-value text-primary"><?= $activeProjects ?></span>
                    <small class="text-muted kpi-sub mt-1">
                        <?php if ($overdue > 0) : ?>
                            <span class="text-danger fw-semibold"><?= $overdue ?> overdue</span><?= $riskUrgent > 0 ? " &middot; {$riskUrgent} berisiko" : '' ?>
                        <?php elseif ($riskUrgent > 0) : ?>
                            <span class="text-warning fw-semibold"><?= $riskUrgent ?> berisiko</span>
                        <?php else : ?>
                            Semua sesuai jadwal
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Apps Managed -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 kpi-card">
                <div class="card-body p-3 d-flex flex-column justify-content-center">
                    <div class="meta-item-label mb-1">Portofolio Aplikasi</div>
                    <span class="kpi-value text-info"><?= $totalApps ?></span>
                    <small class="text-muted kpi-sub mt-1">
                        Sebagai PIC aplikasi
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Performance Analytics Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header pb-0 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0 fs-6 fw-bold">Analisis Kinerja</h5>
            <select id="userChartToggle" class="form-select form-select-sm w-auto py-1">
                <option value="sdlc" selected>Distribusi Fase SDLC</option>
                <option value="trend">Tren Penyelesaian Bulanan</option>
            </select>
        </div>
        <div class="card-body pt-3">
            <!-- SDLC Distribution View -->
            <div id="view-user-sdlc">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-6">
                        <?php if (!empty($sdlc_distribution)) : ?>
                            <div id="chart-user-sdlc"></div>
                        <?php else : ?>
                            <div class="d-flex flex-column align-items-center justify-content-center text-center p-4 border rounded bg-light-subtle h-100" style="min-height: 180px;">
                                <div class="fw-semibold text-muted" style="font-size: 0.85rem;">Tidak Ada Proyek Aktif</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12 col-md-6 border-start-md">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="meta-item-label mb-0">Rincian Fase Aktif</span>
                            <span class="badge bg-light-info text-info border border-info-subtle" style="font-size: 0.7rem;">
                                <?= $activeProjects ?> Proyek Aktif
                            </span>
                        </div>
                        <?php if (!empty($sdlc_distribution)) : ?>
                            <div class="d-flex flex-column gap-2" style="max-height: 160px; overflow-y: auto;">
                                <?php
                                $totalActive = max(1, $activeProjects);
                                foreach ($sdlc_distribution as $phaseName => $count) :
                                    $pct = round(($count / $totalActive) * 100, 1);
                                ?>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold text-dark" style="font-size: 0.8rem;"><?= esc($phaseName) ?></span>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <strong class="text-primary"><?= (int) $count ?></strong> (<?= $pct ?>%)
                                            </small>
                                        </div>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="text-center py-4 text-muted">
                                <small>Tidak ada proyek aktif saat ini.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Monthly Trend View -->
            <div id="view-user-trend" class="d-none">
                <div class="text-center mb-1">
                    <span class="meta-item-label">Tren Penyelesaian 6 Bulan Terakhir</span>
                </div>
                <div id="chart-user-trend"></div>
            </div>
        </div>
    </div>

    <!-- Row 3: Assigned Projects (Tabbed: Active / Completed) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header pb-0 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0 fs-6 fw-bold">Daftar Proyek Ditugaskan</h5>
            <ul class="nav nav-pills nav-sm gap-1 mb-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-1 px-3" id="tab-active-btn" data-bs-toggle="pill" data-bs-target="#tab-active-projects" type="button" role="tab" style="font-size: 0.8rem;">
                        Aktif <span class="badge bg-info ms-1"><?= count($activeProjectsList) ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-3" id="tab-completed-btn" data-bs-toggle="pill" data-bs-target="#tab-completed-projects" type="button" role="tab" style="font-size: 0.8rem;">
                        Selesai <span class="badge bg-success ms-1"><?= count($completedProjectsList) ?></span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body pt-3 p-0">
            <div class="tab-content">
                <!-- Active Projects Tab -->
                <div class="tab-pane fade show active" id="tab-active-projects" role="tabpanel">
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Nama Proyek</th>
                                    <th>Status SDLC</th>
                                    <th>Tenggat Waktu</th>
                                    <th class="text-center user-project-actions pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($activeProjectsList)) : ?>
                                    <?php foreach ($activeProjectsList as $project) : ?>
                                        <tr>
                                            <td class="ps-4">
                                                <strong class="text-body d-block"><?= esc($project['name'] ?? '-') ?></strong>
                                                <span class="badge bg-light-primary text-primary border border-primary-subtle user-project-code"><?= esc($project['project_code'] ?? '-') ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $statusBadge($project['status'] ?? null) ?> fw-semibold"><?= esc($project['status'] ?? '-') ?></span>
                                                <?php if (!empty($project['deadline_label'])) : ?>
                                                    <span class="badge bg-light-<?= esc($project['deadline_class'] ?? 'secondary') ?> text-<?= esc($project['deadline_class'] ?? 'secondary') ?> border border-<?= esc($project['deadline_class'] ?? 'secondary') ?>-subtle d-block mt-1 fw-semibold" style="width: fit-content; font-size: 0.7rem;">
                                                        <?= esc($project['deadline_label']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-body"><?= $dateValue($project['end_date'] ?? null) ?></span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <a href="<?= base_url('/projects/detail/' . (int) $project['id']) ?>" class="btn btn-sm btn-outline-primary px-3 py-1 fw-semibold">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Tidak ada proyek aktif yang ditugaskan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards View -->
                    <div class="d-block d-md-none p-3">
                        <?php if (!empty($activeProjectsList)) : ?>
                            <?php foreach ($activeProjectsList as $project) : ?>
                                <div class="user-project-mobile-card">
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                        <div class="min-width-0 flex-grow-1">
                                            <strong class="text-body d-block fs-6 mb-1 text-break"><?= esc($project['name'] ?? '-') ?></strong>
                                            <span class="badge bg-light-primary text-primary border border-primary-subtle user-project-code"><?= esc($project['project_code'] ?? '-') ?></span>
                                        </div>
                                        <span class="badge <?= $statusBadge($project['status'] ?? null) ?> fw-semibold flex-shrink-0"><?= esc($project['status'] ?? '-') ?></span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between pt-2 mt-2 border-top">
                                        <div>
                                            <div class="meta-item-label" style="font-size: 0.7rem; margin-bottom: 0.1rem;">Tenggat Waktu</div>
                                            <span class="fw-semibold text-body small"><?= $dateValue($project['end_date'] ?? null) ?></span>
                                            <?php if (!empty($project['deadline_label'])) : ?>
                                                <span class="badge bg-light-<?= esc($project['deadline_class'] ?? 'secondary') ?> text-<?= esc($project['deadline_class'] ?? 'secondary') ?> border border-<?= esc($project['deadline_class'] ?? 'secondary') ?>-subtle d-inline-block ms-1 fw-semibold" style="font-size: 0.65rem;">
                                                    <?= esc($project['deadline_label']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?= base_url('/projects/detail/' . (int) $project['id']) ?>" class="btn btn-sm btn-outline-primary px-3 py-1 fw-semibold">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="text-center py-4 text-muted small">Tidak ada proyek aktif yang ditugaskan.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Completed Projects Tab -->
                <div class="tab-pane fade" id="tab-completed-projects" role="tabpanel">
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Nama Proyek</th>
                                    <th>Status</th>
                                    <th>Tanggal Selesai</th>
                                    <th class="text-center user-project-actions pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($completedProjectsList)) : ?>
                                    <?php foreach ($completedProjectsList as $project) : ?>
                                        <?php
                                        $isOnTime = !empty($project['promote_date']) && !empty($project['end_date']) && $project['promote_date'] <= $project['end_date'];
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <strong class="text-body d-block"><?= esc($project['name'] ?? '-') ?></strong>
                                                <span class="badge bg-light-primary text-primary border border-primary-subtle user-project-code"><?= esc($project['project_code'] ?? '-') ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success text-white fw-semibold">Selesai</span>
                                                <span class="badge <?= $isOnTime ? 'bg-light-success text-success border border-success-subtle' : 'bg-light-danger text-danger border border-danger-subtle' ?> d-block mt-1 fw-semibold" style="width: fit-content; font-size: 0.7rem;">
                                                    <?= $isOnTime ? 'Tepat Waktu' : 'Terlambat' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-body"><?= $dateValue($project['promote_date'] ?? null) ?></span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <a href="<?= base_url('/projects/detail/' . (int) $project['id']) ?>" class="btn btn-sm btn-outline-primary px-3 py-1 fw-semibold">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Belum ada proyek yang diselesaikan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards View -->
                    <div class="d-block d-md-none p-3">
                        <?php if (!empty($completedProjectsList)) : ?>
                            <?php foreach ($completedProjectsList as $project) : ?>
                                <?php
                                $isOnTime = !empty($project['promote_date']) && !empty($project['end_date']) && $project['promote_date'] <= $project['end_date'];
                                ?>
                                <div class="user-project-mobile-card">
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                        <div class="min-width-0 flex-grow-1">
                                            <strong class="text-body d-block fs-6 mb-1 text-break"><?= esc($project['name'] ?? '-') ?></strong>
                                            <span class="badge bg-light-primary text-primary border border-primary-subtle user-project-code"><?= esc($project['project_code'] ?? '-') ?></span>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <span class="badge bg-success text-white fw-semibold">Selesai</span>
                                            <span class="badge <?= $isOnTime ? 'bg-light-success text-success border border-success-subtle' : 'bg-light-danger text-danger border border-danger-subtle' ?> d-block mt-1 fw-semibold" style="font-size: 0.65rem;">
                                                <?= $isOnTime ? 'Tepat Waktu' : 'Terlambat' ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between pt-2 mt-2 border-top">
                                        <div>
                                            <div class="meta-item-label" style="font-size: 0.7rem; margin-bottom: 0.1rem;">Tanggal Selesai</div>
                                            <span class="fw-semibold text-body small"><?= $dateValue($project['promote_date'] ?? null) ?></span>
                                        </div>
                                        <a href="<?= base_url('/projects/detail/' . (int) $project['id']) ?>" class="btn btn-sm btn-outline-primary px-3 py-1 fw-semibold">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="text-center py-4 text-muted small">Belum ada proyek yang diselesaikan.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$isProtectedUser) : ?>
<!-- Modal Reset Password -->
<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-2">Reset Password Pengguna</h6>
                <p class="text-muted mb-0">Password pengguna <strong><?= esc($user['name'] ?? '-') ?></strong> akan direset ke password default sistem. Lanjutkan?</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/users/reset-password/' . (int) $user['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-warning">Ya, Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Deactivate User -->
<div class="modal fade" id="modalDeactivateUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-2">Nonaktifkan Pengguna</h6>
                <p class="text-muted mb-0">Pengguna <strong><?= esc($user['name'] ?? '-') ?></strong> akan dinonaktifkan. Data historis penugasan tetap dipertahankan di sistem.</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/users/deactivate/' . (int) $user['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" data-cooldown="3">Ya, Nonaktifkan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Activate User -->
<div class="modal fade" id="modalActivateUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-2">Aktifkan Pengguna</h6>
                <p class="text-muted mb-0">Pengguna <strong><?= esc($user['name'] ?? '-') ?></strong> akan diaktifkan kembali ke sistem.</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/users/activate/' . (int) $user['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-success">Ya, Aktifkan Pengguna</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function getChartThemeOptions() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const textColor = isDark ? '#f5f7ff' : '#25396f';
            const mutedColor = isDark ? '#a6a8b8' : '#607080';
            const gridColor = isDark ? '#2b2b40' : '#e6eaee';
            return {
                chart: {
                    foreColor: textColor
                },
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light'
                },
                legend: {
                    labels: {
                        colors: textColor
                    }
                },
                grid: {
                    borderColor: gridColor
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: mutedColor
                        }
                    },
                    axisBorder: {
                        color: gridColor
                    },
                    axisTicks: {
                        color: gridColor
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: mutedColor
                        }
                    }
                }
            };
        }

        const sdlcData = <?= json_encode($sdlc_distribution ?? []) ?>;
        const sdlcLabels = Object.keys(sdlcData);
        const sdlcSeries = Object.values(sdlcData);

        const trendMonths = <?= json_encode($completion_chart['months'] ?? []) ?>;
        const trendOnTime = <?= json_encode($completion_chart['on_time'] ?? []) ?>;
        const trendLate = <?= json_encode($completion_chart['late'] ?? []) ?>;

        let chartSdlc = null;
        let chartTrend = null;

        const renderUserCharts = async () => {
            if (chartSdlc) {
                chartSdlc.destroy();
                chartSdlc = null;
            }
            if (chartTrend) {
                chartTrend.destroy();
                chartTrend = null;
            }

            const elSdlc = document.querySelector("#chart-user-sdlc");
            if (elSdlc) elSdlc.innerHTML = '';
            const elTrend = document.querySelector("#chart-user-trend");
            if (elTrend) elTrend.innerHTML = '';

            const themeOpts = getChartThemeOptions();

            // SDLC Distribution Donut
            if (sdlcSeries.length > 0 && elSdlc) {
                chartSdlc = new ApexCharts(elSdlc, {
                    chart: {
                        type: 'donut',
                        height: 220,
                        ...themeOpts.chart
                    },
                    series: sdlcSeries,
                    labels: sdlcLabels,
                    colors: ['#435ebe', '#57caeb', '#5ddab4', '#ff7976', '#ffc107'],
                    theme: themeOpts.theme,
                    tooltip: themeOpts.tooltip,
                    legend: {
                        position: 'bottom',
                        fontSize: '11px',
                        ...themeOpts.legend
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            colors: ['#ffffff']
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    show: false
                                }
                            }
                        }
                    }
                });
                await chartSdlc.render();
            }

            // Monthly Completion Trend (Stacked Bar)
            if (elTrend) {
                chartTrend = new ApexCharts(elTrend, {
                    chart: {
                        type: 'bar',
                        height: 220,
                        stacked: true,
                        toolbar: {
                            show: false
                        },
                        ...themeOpts.chart
                    },
                    series: [{
                            name: 'Tepat Waktu',
                            data: trendOnTime
                        },
                        {
                            name: 'Terlambat',
                            data: trendLate
                        }
                    ],
                    colors: ['#198754', '#dc3545'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '50%',
                            borderRadius: 3
                        }
                    },
                    xaxis: {
                        categories: trendMonths,
                        ...themeOpts.xaxis
                    },
                    yaxis: {
                        ...themeOpts.yaxis,
                        labels: {
                            ...themeOpts.yaxis.labels,
                            formatter: function(val) {
                                return Math.floor(val);
                            }
                        }
                    },
                    grid: themeOpts.grid,
                    legend: {
                        position: 'top',
                        fontSize: '11px',
                        ...themeOpts.legend
                    },
                    tooltip: {
                        ...themeOpts.tooltip,
                        y: {
                            formatter: function(val) {
                                return val + ' project';
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    theme: themeOpts.theme
                });
                await chartTrend.render();
            }
        };

        renderUserCharts();

        const toggleDark = document.getElementById('toggle-dark');
        if (toggleDark) {
            toggleDark.addEventListener('change', function() {
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    renderUserCharts();
                }));
            });
        }

        // Chart Toggle
        const userToggle = document.getElementById('userChartToggle');
        if (userToggle) {
            userToggle.addEventListener('change', function() {
                const isSdlc = this.value === 'sdlc';
                document.getElementById('view-user-sdlc').classList.toggle('d-none', !isSdlc);
                document.getElementById('view-user-trend').classList.toggle('d-none', isSdlc);
                window.setTimeout(() => {
                    if (isSdlc && chartSdlc) chartSdlc.resize();
                    if (!isSdlc && chartTrend) chartTrend.resize();
                }, 50);
            });
        }
    });
</script>

<?= $this->endSection() ?>