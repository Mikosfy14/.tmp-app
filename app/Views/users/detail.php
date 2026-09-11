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

$dateValue = static fn($value): string => !empty($value) ? date('d M Y', strtotime($value)) : '-';
$valueOrDash = static fn($value): string => $value !== null && $value !== '' ? esc($value) : '-';
$isActive = (int) ($user['is_active'] ?? 0) === 1;
$isCurrentUser = (int) session()->get('user_id') === (int) ($user['id'] ?? 0);

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

<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    .user-detail-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
    }

    .kpi-card .kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .kpi-card .kpi-sub {
        font-size: 0.74rem;
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
    <!-- Row 1: Profile Card + KPI Cards -->
    <div class="row g-3 mb-3">
        <!-- Profile Info Card -->
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

        <!-- KPI Cards -->
        <div class="col-12 col-xl-8">
            <div class="row g-3 h-100">
                <!-- Completion Rate -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm h-100 kpi-card">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <small class="text-muted d-block mb-1">Completion Rate</small>
                            <span class="kpi-value <?= $completionRate >= 70 ? 'text-success' : ($completionRate >= 40 ? 'text-warning' : 'text-danger') ?>">
                                <?= $completionRate ?>%
                            </span>
                            <small class="text-muted kpi-sub mt-1">
                                <?= $totalCompleted ?> / <?= $totalAll ?> project
                            </small>
                        </div>
                    </div>
                </div>

                <!-- On-Time Rate -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm h-100 kpi-card">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <small class="text-muted d-block mb-1">On-Time Rate</small>
                            <span class="kpi-value <?= $onTimeRate >= 70 ? 'text-success' : ($onTimeRate >= 40 ? 'text-warning' : 'text-danger') ?>">
                                <?= $onTimeRate ?>%
                            </span>
                            <small class="text-muted kpi-sub mt-1">
                                <?= $onTimeDone ?> tepat waktu, <?= $lateDone ?> terlambat
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Active Projects -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm h-100 kpi-card">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <small class="text-muted d-block mb-1">Project Aktif</small>
                            <span class="kpi-value text-info"><?= $activeProjects ?></span>
                            <small class="text-muted kpi-sub mt-1">
                                <?php if ($overdue > 0) : ?>
                                    <span class="text-danger fw-semibold"><?= $overdue ?> overdue</span><?= $riskUrgent > 0 ? " · {$riskUrgent} berisiko" : '' ?>
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
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm h-100 kpi-card">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <small class="text-muted d-block mb-1">Aplikasi Dikelola</small>
                            <span class="kpi-value text-primary"><?= $totalApps ?></span>
                            <small class="text-muted kpi-sub mt-1">
                                Sebagai PIC aplikasi
                            </small>
                        </div>
                    </div>
                </div>

                <!-- SDLC Distribution + Completion Trend -->
                <div class="col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Analisis Kinerja</h6>
                            <select id="userChartToggle" class="form-select form-select-sm w-auto py-1">
                                <option value="sdlc" selected>Distribusi Fase SDLC</option>
                                <option value="trend">Tren Penyelesaian Bulanan</option>
                            </select>
                        </div>
                        <div class="card-body p-3">
                            <!-- SDLC Distribution View -->
                            <div id="view-user-sdlc">
                                <div class="row g-3 align-items-center">
                                    <div class="col-12 col-md-6">
                                        <?php if (!empty($sdlc_distribution)) : ?>
                                            <div id="chart-user-sdlc"></div>
                                        <?php else : ?>
                                            <div class="d-flex flex-column align-items-center justify-content-center text-center p-4 border rounded bg-light-subtle h-100" style="min-height: 180px;">
                                                <div class="fw-semibold text-muted" style="font-size: 0.85rem;">Tidak Ada Project Aktif</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-md-6 border-start-md">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Rincian Fase Aktif</small>
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
                                                <small>Tidak ada project aktif saat ini.</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Trend View -->
                            <div id="view-user-trend" class="d-none">
                                <div class="text-center mb-1">
                                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Tren Penyelesaian 6 Bulan Terakhir</small>
                                </div>
                                <div id="chart-user-trend"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Assigned Projects (Tabbed: Active / Completed) -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-kanban me-2 text-primary"></i>Assigned Projects</h5>
                    <ul class="nav nav-pills nav-sm gap-1 mb-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-2" id="tab-active-btn" data-bs-toggle="pill" data-bs-target="#tab-active-projects" type="button" role="tab" style="font-size: 0.78rem;">
                                Aktif <span class="badge bg-info ms-1"><?= count($activeProjectsList) ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-2" id="tab-completed-btn" data-bs-toggle="pill" data-bs-target="#tab-completed-projects" type="button" role="tab" style="font-size: 0.78rem;">
                                Selesai <span class="badge bg-success ms-1"><?= count($completedProjectsList) ?></span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- Active Projects Tab -->
                        <div class="tab-pane fade show active" id="tab-active-projects" role="tabpanel">
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
                                        <?php if (!empty($activeProjectsList)) : ?>
                                            <?php foreach ($activeProjectsList as $project) : ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <strong class="text-dark d-block"><?= esc($project['name'] ?? '-') ?></strong>
                                                        <span class="badge bg-light-secondary text-muted"><?= esc($project['project_code'] ?? '-') ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= $statusBadge($project['status'] ?? null) ?>"><?= esc($project['status'] ?? '-') ?></span>
                                                        <?php if (!empty($project['deadline_label'])) : ?>
                                                            <span class="badge bg-light-<?= esc($project['deadline_class'] ?? 'secondary') ?> text-<?= esc($project['deadline_class'] ?? 'secondary') ?> d-block mt-1" style="width: fit-content; font-size: 0.72rem;">
                                                                <?= esc($project['deadline_label']) ?>
                                                            </span>
                                                        <?php endif; ?>
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
                                                <td colspan="4" class="text-center py-4 text-muted">Tidak ada project aktif yang ditugaskan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Completed Projects Tab -->
                        <div class="tab-pane fade" id="tab-completed-projects" role="tabpanel">
                            <div class="table-responsive">
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
                                                        <strong class="text-dark d-block"><?= esc($project['name'] ?? '-') ?></strong>
                                                        <span class="badge bg-light-secondary text-muted"><?= esc($project['project_code'] ?? '-') ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">Selesai</span>
                                                        <span class="badge <?= $isOnTime ? 'bg-light-success text-success' : 'bg-light-danger text-danger' ?> d-block mt-1" style="width: fit-content; font-size: 0.72rem;">
                                                            <?= $isOnTime ? 'Tepat Waktu' : 'Terlambat' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong class="text-dark"><?= $dateValue($project['promote_date'] ?? null) ?></strong>
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
                                                <td colspan="4" class="text-center py-4 text-muted">Belum ada project yang diselesaikan.</td>
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
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
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
            <div class="modal-body">
                <p class="mb-0">User <strong><?= esc($user['name'] ?? '-') ?></strong> akan dibuat nonaktif. Data historis tetap dipertahankan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/users/deactivate/' . (int) $user['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger" data-cooldown="3">Deactivate User</button>
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