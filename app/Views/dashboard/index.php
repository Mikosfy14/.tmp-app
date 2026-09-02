<?php

/**
 * View: Dashboard Index
 * @var string $title
 * @var string $name
 * @var string $role_name
 * @var string $category
 * @var array $my_stats
 * @var array $my_active_projects
 * @var array $completion_chart
 * @var array $my_timeline
 * @var array $team_stats
 * @var array $team_members
 */
helper('deadline');
$deadlineAlerts = get_user_deadline_notifications();
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="page-heading">
    <h3>Dashboard</h3>
</div>

<div class="page-content">
    <?php if (!empty($deadlineAlerts)) : ?>
        <!-- Windowed Deadline Alert Modal (Centered) -->
        <div class="modal fade" id="modalWindowedDeadlineAlert" tabindex="-1" aria-labelledby="modalDeadlineAlertLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white d-flex align-items-center mb-0" id="modalDeadlineAlertLabel">
                            Peringatan Tenggat Waktu (Deadline Alert)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border d-flex align-items-center mb-3">
                            <div>
                                <strong class="d-block text-dark">Ada <?= count($deadlineAlerts) ?> project yang memerlukan perhatian segera!</strong>
                                <small class="text-muted">Daftar project di bawah ini memiliki status Overdue atau mendekati batas akhir penyelesaian.</small>
                            </div>
                        </div>

                        <div class="list-group list-group-flush border rounded" style="max-height: 360px; overflow-y: auto;">
                            <?php foreach ($deadlineAlerts as $alertItem) : ?>
                                <?php
                                $daysLeft = $alertItem['days_left'] ?? null;
                                $alertRelativeText = null;
                                $alertRelativeClass = 'text-muted';
                                if ($daysLeft !== null) {
                                    if ($daysLeft < 0) {
                                        $alertRelativeText = abs($daysLeft) . ' hari terlambat';
                                        $alertRelativeClass = 'text-danger fw-semibold';
                                    } elseif ($daysLeft === 0) {
                                        $alertRelativeText = 'Tenggat hari ini';
                                        $alertRelativeClass = 'text-danger fw-bold';
                                    } else {
                                        $alertRelativeText = 'Sisa ' . $daysLeft . ' hari';
                                        $alertRelativeClass = !empty($alertItem['deadline_class']) ? 'text-' . esc($alertItem['deadline_class']) . ' fw-semibold' : 'text-muted';
                                    }
                                }
                                ?>
                                <div class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3">
                                    <div class="min-width-0">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-light-<?= esc($alertItem['deadline_class']) ?> text-<?= esc($alertItem['deadline_class']) ?> fw-bold"><?= esc($alertItem['deadline_label']) ?></span>
                                            <strong class="text-dark text-truncate"><?= esc($alertItem['name']) ?></strong>
                                        </div>
                                        <div class="text-muted small d-flex align-items-center gap-2 flex-wrap">
                                            <span><i class="bi bi-tag me-1"></i><?= esc($alertItem['project_code']) ?></span> &middot;
                                            <span><i class="bi bi-calendar-event me-1"></i>Tenggat: <strong class="text-dark"><?= !empty($alertItem['end_date']) ? date('d M Y', strtotime($alertItem['end_date'])) : '-' ?></strong></span>
                                            <?php if ($alertRelativeText !== null) : ?>
                                                &middot; <span class="<?= $alertRelativeClass ?>"><i class="bi bi-clock-history me-1"></i><?= esc($alertRelativeText) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <a href="<?= base_url('/projects/detail/' . $alertItem['id']) ?>" class="btn btn-sm btn-outline-primary text-nowrap align-self-start align-self-md-center">
                                        <i class="bi bi-eye-fill me-1"></i> Buka Project
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer bg-light d-flex justify-content-end">
                        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Saya Mengerti</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- User Profile & Status Header -->
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4 flex-shrink-0" style="width: 52px; height: 52px; border-radius: 50%;">
                    <?= strtoupper(substr(session()->get('name') ?? 'U', 0, 1)) ?>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="mb-0 fw-bold text-dark">Selamat Datang, <?= esc(session()->get('name')) ?>!</h5>
                            <span class="badge bg-light-primary text-primary"><?= esc(session()->get('role_name')) ?></span>
                            <span class="badge bg-light-info text-info"><?= esc(session()->get('category') ?? 'Organik') ?></span>
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                        </div>
                        <span class="badge bg-light-secondary text-muted"><?= date('l, d F Y') ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap text-sm text-muted mt-1">
                        <span><i class="bi bi-briefcase me-1 text-primary"></i><?= esc($user_detail['job_title'] ?? 'Staff Developer') ?></span>
                        <span><i class="bi bi-person me-1 text-primary"></i>@<?= esc(session()->get('username')) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Priority Projects & KPI Sub-Info Calculation -->
    <?php
    $currentUserId = (int) session()->get('user_id');
    $my_managed_apps = $my_managed_apps ?? [];
    $my_active_projects = $my_active_projects ?? [];
    $my_stats = $my_stats ?? [];

    // 1. Project Roles Calculation (PIC Utama vs Anggota Tim)
    $picMainCount = 0;
    $teamMemberCount = 0;
    foreach ($my_active_projects as $p) {
        if (strtolower($p['status'] ?? '') === 'completed') {
            continue;
        }
        $assignedIds = array_values(array_filter(array_map('intval', explode(',', (string) ($p['assigned_to'] ?? '')))));
        $firstAssigned = $assignedIds[0] ?? null;
        $createdBy = (int) ($p['created_by'] ?? 0);

        if ($firstAssigned === $currentUserId || ($firstAssigned === null && $createdBy === $currentUserId)) {
            $picMainCount++;
        } else {
            $teamMemberCount++;
        }
    }

    if ($picMainCount > 0 && $teamMemberCount > 0) {
        $projectRoleText = "{$picMainCount} PIC Utama · {$teamMemberCount} Anggota Tim";
    } elseif ($picMainCount > 0) {
        $projectRoleText = "{$picMainCount} Sebagai PIC Utama";
    } elseif ($teamMemberCount > 0) {
        $projectRoleText = "{$teamMemberCount} Sebagai Anggota Tim";
    } else {
        $projectRoleText = "-";
    }

    // 2. Application Criticality Summary
    $managedAppsCount = count($my_managed_apps ?? []);
    if ($managedAppsCount === 0) {
        $appCriticalityText = "-";
    } elseif ($managedAppsCount === 1) {
        $critName = trim((string) ($my_managed_apps[0]['criticality_name'] ?? ''));
        $appCriticalityText = $critName !== '' ? "Criticality: {$critName}" : "Tingkat Standar";
    } else {
        $critGroups = [];
        foreach ($my_managed_apps as $app) {
            $cName = trim((string) ($app['criticality_name'] ?? ''));
            if ($cName !== '') {
                $critGroups[$cName] = ($critGroups[$cName] ?? 0) + 1;
            }
        }
        if (!empty($critGroups)) {
            $formattedGroups = [];
            foreach ($critGroups as $cName => $cCount) {
                $formattedGroups[] = "{$cCount} {$cName}";
            }
            $appCriticalityText = implode(' · ', $formattedGroups);
        } else {
            $appCriticalityText = "{$managedAppsCount} Aplikasi Aktif";
        }
    }

    // 3. Completion Rates Summary (Tepat Waktu vs Terlambat)
    $totalCompleted = (int) ($my_stats['total_completed'] ?? 0);
    $onTimeDone = (int) ($my_stats['on_time_done'] ?? 0);
    $lateDone = max(0, $totalCompleted - $onTimeDone);
    $completionSummaryText = "{$onTimeDone} Tepat Waktu · {$lateDone} Terlambat";

    // Priority Projects
    $priority_projects = array_filter($my_active_projects, function ($p) {
        return in_array($p['deadline_label'] ?? '', ['Risk', 'Urgent', 'Critical', 'Overdue']);
    });
    $has_priority = !empty($priority_projects);
    if ($has_priority) {
        $display_priority_projects = $priority_projects;
        $is_priority_fallback = false;
    } else {
        $active_only = array_filter($my_active_projects, function ($p) {
            return strtolower($p['status'] ?? '') !== 'completed';
        });
        usort($active_only, function ($a, $b) {
            $tA = !empty($a['end_date']) ? strtotime($a['end_date']) : PHP_INT_MAX;
            $tB = !empty($b['end_date']) ? strtotime($b['end_date']) : PHP_INT_MAX;
            return $tA <=> $tB;
        });
        $display_priority_projects = array_slice($active_only, 0, 5);
        $is_priority_fallback = true;
    }
    ?>

    <!-- 3 KPI Counters -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <small class="text-muted d-block">Project Aktif</small>
                    <h3 class="text-info mb-0"><?= (int) $my_stats['active_projects'] ?></h3>
                    <small class="text-muted d-block mt-1 text-truncate" title="<?= esc($projectRoleText) ?>" style="font-size: 0.78rem;">
                        <i class="bi bi-person-badge me-1 text-info"></i><?= esc($projectRoleText) ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <small class="text-muted d-block">Aplikasi Dikelola</small>
                    <h3 class="text-primary mb-0"><?= (int) ($my_stats['total_apps_managed'] ?? 0) ?></h3>
                    <small class="text-muted d-block mt-1 text-truncate" title="<?= esc($appCriticalityText) ?>" style="font-size: 0.78rem;">
                        <i class="bi bi-shield-check me-1 text-primary"></i><?= esc($appCriticalityText) ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <small class="text-muted d-block">Total Selesai</small>
                    <h3 class="text-success mb-0"><?= (int) $my_stats['total_completed'] ?></h3>
                    <small class="text-muted d-block mt-1 text-truncate" title="<?= esc($completionSummaryText) ?>" style="font-size: 0.78rem;">
                        <i class="bi bi-clock-history me-1 text-success"></i><?= esc($completionSummaryText) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Work Area -->
    <div class="row g-3 mb-3">
        <!-- Left Column: Priority Tasks & Workload Analysis -->
        <div class="col-12 col-xl-7">
            <!-- Priority Tasks -->
            <div class="card shadow-sm mb-3">
                <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="card-title mb-0 fs-6 fw-bold text-dark">Priority Tasks</h5>
                        <small class="text-muted d-none d-sm-inline">| Proyek Mendekati Tenggat / Overdue</small>
                    </div>
                    <?php if ($has_priority) : ?>
                        <span class="badge bg-light-danger text-danger border border-danger-subtle py-1 px-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalWindowedDeadlineAlert" role="button" style="cursor: pointer;" title="Klik untuk membuka jendela peringatan deadline">
                            <i class="bi bi-bell-fill me-1"></i><?= count($priority_projects) ?> Perlu Perhatian
                        </span>
                    <?php else : ?>
                        <span class="badge bg-light-success text-success border border-success-subtle py-1 px-2 fw-semibold">
                            Semua Aman
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 dashboard-project-table">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3 py-2" style="width: 40%;">Project Name</th>
                                    <th class="py-2" style="width: 25%;">Status & Deadline</th>
                                    <th class="py-2" style="width: 23%;">Tenggat</th>
                                    <th class="text-center pe-3 py-2" style="width: 12%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($display_priority_projects)): ?>
                                    <?php foreach ($display_priority_projects as $prj) : ?>
                                        <?php
                                        $relativeDeadlineText = '-';
                                        $relativeDeadlineClass = 'text-muted';
                                        if (!empty($prj['end_date'])) {
                                            $today = new DateTimeImmutable(date('Y-m-d'));
                                            $targetDate = new DateTimeImmutable(date('Y-m-d', strtotime($prj['end_date'])));
                                            $diff = (int) $today->diff($targetDate)->format('%r%a');
                                            if ($diff < 0) {
                                                $relativeDeadlineText = abs($diff) . ' hari terlambat';
                                                $relativeDeadlineClass = 'text-danger fw-semibold';
                                            } elseif ($diff === 0) {
                                                $relativeDeadlineText = 'Tenggat hari ini';
                                                $relativeDeadlineClass = 'text-danger fw-bold';
                                            } else {
                                                $relativeDeadlineText = 'Sisa ' . $diff . ' hari';
                                                $relativeDeadlineClass = !empty($prj['deadline_class']) ? 'text-' . esc($prj['deadline_class']) . ' fw-semibold' : 'text-muted';
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td class="ps-3 py-2">
                                                <strong class="d-block text-dark text-truncate" style="max-width: 220px;" title="<?= esc($prj['name']) ?>"><?= esc($prj['name']) ?></strong>
                                                <small class="text-muted"><?= esc($prj['project_code']) ?></small>
                                            </td>
                                            <td class="py-2">
                                                <span class="badge <?= esc($prj['status_class']) ?>"><?= esc($prj['status']) ?></span>
                                                <?php if (!empty($prj['deadline_label'])) : ?>
                                                    <span class="badge bg-light-<?= esc($prj['deadline_class']) ?> text-<?= esc($prj['deadline_class']) ?> d-block mt-1" style="width: fit-content; font-size: 0.72rem;">
                                                        <?= esc($prj['deadline_label']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-2">
                                                <small class="d-block text-muted"><i class="bi bi-calendar-event me-1"></i><?= !empty($prj['end_date']) ? date('d M Y', strtotime($prj['end_date'])) : '-' ?></small>
                                                <?php if (!empty($prj['end_date'])) : ?>
                                                    <small class="d-block <?= $relativeDeadlineClass ?>" style="font-size: 0.72rem;">
                                                        <?= esc($relativeDeadlineText) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center pe-3 py-2">
                                                <a href="<?= base_url('/projects/detail/' . $prj['id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 text-nowrap" title="Detail Project">
                                                    <i class="bi bi-eye-fill"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">
                                            Belum ada project aktif saat ini.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Workload Analysis Chart -->
            <div class="card shadow-sm mb-0">
                <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="card-title mb-0 fs-6 fw-bold text-dark">Analisis Beban Kerja</h5>
                    </div>
                    <select id="personalChartToggle" class="form-select form-select-sm w-auto py-1">
                        <option value="sdlc" selected>Distribusi Fase SDLC</option>
                        <option value="rates">Penyelesaian & Ketepatan Waktu</option>
                        <option value="trend">Tren Penyelesaian Bulanan</option>
                    </select>
                </div>
                <div class="card-body p-3">
                    <!-- View 1: SDLC Distribution (Active Default) -->
                    <div id="view-sdlc">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-6">
                                <div id="chart-personal-sdlc"></div>
                            </div>
                            <div class="col-12 col-md-6 border-start-md">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Rincian Fase Aktif</small>
                                    <span class="badge bg-light-info text-info border border-info-subtle" style="font-size: 0.7rem;">
                                        <?= (int) $my_stats['active_projects'] ?> Proyek Aktif
                                    </span>
                                </div>
                                <?php if (!empty($sdlc_distribution)) : ?>
                                    <div class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                                        <?php 
                                        $totalActive = max(1, (int) $my_stats['active_projects']);
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

                    <!-- View 2: Completion & On-Time Rates (d-none) -->
                    <div id="view-rates" class="d-none">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-6">
                                <div class="text-center mb-1">
                                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Completion Rate</small>
                                </div>
                                <div id="chart-personal-completion"></div>
                            </div>
                            <div class="col-12 col-md-6 border-start-md">
                                <div class="text-center mb-1">
                                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Ketepatan Waktu</small>
                                </div>
                                <div id="chart-personal-ontime"></div>
                            </div>
                        </div>
                    </div>

                    <!-- View 3: Monthly Trend (d-none) -->
                    <div id="view-trend" class="d-none">
                        <div class="text-center mb-1">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Tren Penyelesaian 6 Bulan Terakhir</small>
                        </div>
                        <div id="chart-personal-trend"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Aplikasi yang Dikelola & Pusat Aksi / Berkas Project -->
        <div class="col-12 col-xl-5">
            <!-- Card 1: Aplikasi yang Dikelola -->
            <div class="card shadow-sm mb-3">
                <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="card-title mb-0 fs-6 fw-bold text-dark">Aplikasi yang Dikelola</h5>
                        <small class="text-muted d-none d-sm-inline">| Penugasan Sebagai PIC</small>
                    </div>
                    <span class="badge bg-light-primary text-primary border border-primary-subtle py-1 px-2 fw-semibold">
                        <?= count($my_managed_apps ?? []) ?> Aplikasi
                    </span>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($my_managed_apps)) : ?>
                        <div class="d-flex flex-column gap-2" style="max-height: 260px; overflow-y: auto;">
                            <?php foreach ($my_managed_apps as $app) : ?>
                                <div class="p-2 border rounded d-flex justify-content-between align-items-center">
                                    <div class="min-width-0 me-2">
                                        <div class="fw-bold text-dark text-truncate text-sm" title="<?= esc($app['app_component']) ?>">
                                            <?= esc($app['app_component']) ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                            <?php if (!empty($app['criticality_name'])) : ?>
                                                <span class="badge bg-light-warning text-dark border border-warning" style="font-size: 0.68rem;">
                                                    <?= esc($app['criticality_name']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($app['platform'])) : ?>
                                                <span class="badge bg-light-secondary text-muted" style="font-size: 0.68rem;">
                                                    <?= esc($app['platform']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        <?php if (!empty($app['url_prod'])) : ?>
                                            <a href="<?= esc($app['url_prod']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Buka URL Produksi">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('/aplikasi/detail/' . $app['id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2" title="Detail Aplikasi">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-3 text-muted">
                            <p class="mb-1 text-sm">Belum ada aplikasi yang ditugaskan sebagai PIC.</p>
                            <a href="<?= base_url('/aplikasi') ?>" class="btn btn-sm btn-outline-primary py-0 px-2">
                                <i class="bi bi-grid me-1"></i>Katalog Aplikasi
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card 2: Pusat Aksi & Berkas Project -->
            <div class="card shadow-sm mb-0">
                <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="card-title mb-0 fs-6 fw-bold text-dark">Pusat Aksi & Berkas Project</h5>
                        <small class="text-muted d-none d-sm-inline">| Pintasan & Repositori</small>
                    </div>
                </div>
                <div class="card-body p-3">
                    <!-- Quick Actions Grid (2 Buttons) -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <a href="<?= base_url('/projects/create') ?>" class="btn btn-sm btn-primary text-white w-100 d-flex align-items-center justify-content-center gap-2 py-2 text-nowrap">
                                <i class="bi bi-plus-circle-fill lh-1"></i> <span class="fw-semibold">Project Baru</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= base_url('/aplikasi/create') ?>" class="btn btn-sm btn-info text-white w-100 d-flex align-items-center justify-content-center gap-2 py-2 text-nowrap">
                                <i class="bi bi-plus-square-fill lh-1"></i> <span class="fw-semibold">Aplikasi Baru</span>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Project Files -->
                    <div class="border-top pt-2 mt-2">
                        <small class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.72rem;">
                            Berkas Project Terbaru
                        </small>
                        <?php if (!empty($recent_project_files)) : ?>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($recent_project_files as $file) : ?>
                                    <?php
                                    $ext = strtolower($file['file_extension'] ?? '');
                                    $iconClass = 'bi-file-earmark-text text-secondary';
                                    if (in_array($ext, ['pdf'])) $iconClass = 'bi-file-earmark-pdf-fill text-danger';
                                    elseif (in_array($ext, ['doc', 'docx'])) $iconClass = 'bi-file-earmark-word-fill text-primary';
                                    elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) $iconClass = 'bi-file-earmark-excel-fill text-success';
                                    ?>
                                    <div class="p-2 border rounded d-flex justify-content-between align-items-center bg-light-subtle">
                                        <div class="d-flex align-items-center gap-2 min-width-0 me-2 flex-grow-1">
                                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded bg-white border" style="width: 38px; height: 38px;">
                                                <i class="bi <?= $iconClass ?> fs-5 lh-1"></i>
                                            </div>
                                            <div class="min-width-0 flex-grow-1">
                                                <strong class="d-block text-dark text-truncate text-sm mb-1" title="<?= esc($file['original_name']) ?>">
                                                    <?= esc($file['original_name']) ?>
                                                </strong>
                                                <div class="text-muted d-flex align-items-center flex-wrap gap-2" style="font-size: 0.74rem;">
                                                    <span class="text-truncate text-secondary fw-medium me-1" style="max-width: 170px;" title="<?= esc($file['project_name'] ?? '-') ?>">
                                                        <i class="bi bi-folder2 me-1"></i><?= esc($file['project_name'] ?? '-') ?>
                                                    </span>
                                                    <span class="text-nowrap text-muted">
                                                        <i class="bi bi-clock me-1"></i><?= !empty($file['created_at']) ? date('d M Y', strtotime($file['created_at'])) : '-' ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="<?= base_url('/projects/files/' . $file['id'] . '/download') ?>" class="btn btn-sm btn-outline-secondary py-1 px-2 flex-shrink-0 d-inline-flex align-items-center gap-1" title="Unduh Berkas">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="text-center py-3 text-muted small">
                                Belum ada berkas terunggah pada project Anda.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-project-table> :not(caption)>*>* {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
</style>

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
                },
                grid: {
                    borderColor: gridColor
                },
                dataLabels: {
                    style: {
                        colors: [textColor]
                    }
                },
                teamDataLabels: {
                    style: {
                        colors: ['#ffffff']
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                value: {
                                    color: textColor
                                },
                                total: {
                                    color: textColor
                                }
                            }
                        }
                    }
                }
            };
        }

        const sdlcDataRaw = <?= json_encode($sdlc_distribution ?? []) ?>;
        const sdlcLabels = Object.keys(sdlcDataRaw);
        const sdlcSeries = Object.values(sdlcDataRaw);
        const completionRate = <?= (float) ($my_stats['completion_rate'] ?? 0) ?>;
        const onTimeRate = <?= $my_stats['total_completed'] > 0 ? round((($my_stats['on_time_done'] ?? 0) / $my_stats['total_completed']) * 100, 1) : 0.0 ?>;
        const trendMonths = <?= json_encode($completion_chart['months'] ?? []) ?>;
        const trendOnTime = <?= json_encode($completion_chart['on_time'] ?? []) ?>;
        const trendLate = <?= json_encode($completion_chart['late'] ?? []) ?>;

        var personalThemeOptions = getChartThemeOptions();

        // 1. SDLC Chart (Donut)
        var optionsSdlc = {
            chart: {
                type: 'donut',
                height: 240,
                ...personalThemeOptions.chart
            },
            series: sdlcSeries.length > 0 ? sdlcSeries : [1],
            labels: sdlcSeries.length > 0 ? sdlcLabels : ['No Active Projects'],
            colors: ['#435ebe', '#57caeb', '#5ddab4', '#ff7976', '#ffc107'],
            theme: personalThemeOptions.theme,
            tooltip: personalThemeOptions.tooltip,
            legend: {
                position: 'bottom',
                fontSize: '11px',
                ...personalThemeOptions.legend
            },
            dataLabels: {
                enabled: true,
                ...personalThemeOptions.teamDataLabels
            },
            plotOptions: personalThemeOptions.plotOptions
        };
        var chartSdlc = new ApexCharts(document.querySelector("#chart-personal-sdlc"), optionsSdlc);
        chartSdlc.render();

        // 2. Completion Rate Chart (RadialBar Gauge)
        var optionsCompletion = {
            chart: {
                type: 'radialBar',
                height: 220,
                sparkline: {
                    enabled: false
                },
                ...personalThemeOptions.chart
            },
            series: [completionRate],
            colors: [completionRate >= 80 ? '#198754' : (completionRate >= 50 ? '#ffc107' : '#dc3545')],
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    hollow: {
                        size: '62%'
                    },
                    track: {
                        background: personalThemeOptions.grid.borderColor,
                        strokeWidth: '97%'
                    },
                    dataLabels: {
                        name: {
                            show: true,
                            fontSize: '12px',
                            color: personalThemeOptions.theme.mode === 'dark' ? '#a6a8b8' : '#607080',
                            offsetY: 20
                        },
                        value: {
                            offsetY: -15,
                            fontSize: '20px',
                            fontWeight: 700,
                            color: personalThemeOptions.chart.foreColor,
                            formatter: function(val) {
                                return val + '%';
                            }
                        }
                    }
                }
            },
            labels: ['Selesai'],
            theme: personalThemeOptions.theme,
            stroke: {
                dashArray: 3
            }
        };
        var chartCompletion = new ApexCharts(document.querySelector("#chart-personal-completion"), optionsCompletion);
        chartCompletion.render();

        // 3. On-Time Rate Chart (RadialBar Gauge)
        var optionsOntime = {
            chart: {
                type: 'radialBar',
                height: 220,
                sparkline: {
                    enabled: false
                },
                ...personalThemeOptions.chart
            },
            series: [onTimeRate],
            colors: [onTimeRate >= 80 ? '#198754' : (onTimeRate >= 50 ? '#ffc107' : '#dc3545')],
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    hollow: {
                        size: '62%'
                    },
                    track: {
                        background: personalThemeOptions.grid.borderColor,
                        strokeWidth: '97%'
                    },
                    dataLabels: {
                        name: {
                            show: true,
                            fontSize: '12px',
                            color: personalThemeOptions.theme.mode === 'dark' ? '#a6a8b8' : '#607080',
                            offsetY: 20
                        },
                        value: {
                            offsetY: -15,
                            fontSize: '20px',
                            fontWeight: 700,
                            color: personalThemeOptions.chart.foreColor,
                            formatter: function(val) {
                                return val + '%';
                            }
                        }
                    }
                }
            },
            labels: ['Tepat Waktu'],
            theme: personalThemeOptions.theme,
            stroke: {
                dashArray: 3
            }
        };
        var chartOntime = new ApexCharts(document.querySelector("#chart-personal-ontime"), optionsOntime);
        chartOntime.render();

        // 4. Monthly Trend Chart (Stacked Bar)
        var optionsTrend = {
            chart: {
                type: 'bar',
                height: 250,
                stacked: true,
                toolbar: {
                    show: false
                },
                ...personalThemeOptions.chart
            },
            series: [{
                    name: 'Tepat Waktu',
                    data: trendOnTime.length > 0 ? trendOnTime : [0]
                },
                {
                    name: 'Terlambat',
                    data: trendLate.length > 0 ? trendLate : [0]
                }
            ],
            xaxis: {
                categories: trendMonths.length > 0 ? trendMonths : ['-'],
                ...personalThemeOptions.xaxis
            },
            yaxis: {
                ...personalThemeOptions.yaxis,
                labels: {
                    ...personalThemeOptions.yaxis.labels,
                    formatter: function(val) {
                        return Math.round(val);
                    }
                }
            },
            colors: ['#198754', '#dc3545'],
            theme: personalThemeOptions.theme,
            tooltip: personalThemeOptions.tooltip,
            grid: personalThemeOptions.grid,
            legend: {
                position: 'top',
                ...personalThemeOptions.legend
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '40%',
                    borderRadius: 2
                }
            },
            dataLabels: {
                enabled: false
            }
        };
        var chartTrend = new ApexCharts(document.querySelector("#chart-personal-trend"), optionsTrend);
        chartTrend.render();

        const rebuildPersonalCharts = async () => {
            if (chartSdlc) {
                chartSdlc.destroy();
                chartSdlc = null;
            }
            if (chartCompletion) {
                chartCompletion.destroy();
                chartCompletion = null;
            }
            if (chartOntime) {
                chartOntime.destroy();
                chartOntime = null;
            }
            if (chartTrend) {
                chartTrend.destroy();
                chartTrend = null;
            }
            document.querySelector('#chart-personal-sdlc').innerHTML = '';
            document.querySelector('#chart-personal-completion').innerHTML = '';
            document.querySelector('#chart-personal-ontime').innerHTML = '';
            document.querySelector('#chart-personal-trend').innerHTML = '';

            const freshTheme = getChartThemeOptions();

            // 1. SDLC Rebuild
            optionsSdlc.chart = {
                type: 'donut',
                height: 240,
                ...freshTheme.chart
            };
            optionsSdlc.theme = freshTheme.theme;
            optionsSdlc.tooltip = freshTheme.tooltip;
            optionsSdlc.legend = {
                position: 'bottom',
                fontSize: '11px',
                ...freshTheme.legend
            };
            optionsSdlc.dataLabels = {
                enabled: true,
                ...freshTheme.teamDataLabels
            };
            optionsSdlc.plotOptions = freshTheme.plotOptions;

            // 2. Completion Rebuild
            optionsCompletion.chart = {
                type: 'radialBar',
                height: 220,
                ...freshTheme.chart
            };
            optionsCompletion.theme = freshTheme.theme;
            optionsCompletion.plotOptions.radialBar.track.background = freshTheme.grid.borderColor;
            optionsCompletion.plotOptions.radialBar.dataLabels.value.color = freshTheme.chart.foreColor;
            optionsCompletion.plotOptions.radialBar.dataLabels.name.color = freshTheme.theme.mode === 'dark' ? '#a6a8b8' : '#607080';

            // 3. On-Time Rebuild
            optionsOntime.chart = {
                type: 'radialBar',
                height: 220,
                ...freshTheme.chart
            };
            optionsOntime.theme = freshTheme.theme;
            optionsOntime.plotOptions.radialBar.track.background = freshTheme.grid.borderColor;
            optionsOntime.plotOptions.radialBar.dataLabels.value.color = freshTheme.chart.foreColor;
            optionsOntime.plotOptions.radialBar.dataLabels.name.color = freshTheme.theme.mode === 'dark' ? '#a6a8b8' : '#607080';

            // 4. Trend Rebuild
            optionsTrend.chart = {
                type: 'bar',
                height: 250,
                stacked: true,
                toolbar: {
                    show: false
                },
                ...freshTheme.chart
            };
            optionsTrend.theme = freshTheme.theme;
            optionsTrend.tooltip = freshTheme.tooltip;
            optionsTrend.grid = freshTheme.grid;
            optionsTrend.xaxis = {
                categories: trendMonths.length > 0 ? trendMonths : ['-'],
                ...freshTheme.xaxis
            };
            optionsTrend.yaxis = {
                ...freshTheme.yaxis,
                labels: {
                    ...freshTheme.yaxis.labels,
                    formatter: function(val) {
                        return Math.round(val);
                    }
                }
            };
            optionsTrend.legend = {
                position: 'top',
                ...freshTheme.legend
            };

            chartSdlc = new ApexCharts(document.querySelector('#chart-personal-sdlc'), optionsSdlc);
            await chartSdlc.render();

            chartCompletion = new ApexCharts(document.querySelector('#chart-personal-completion'), optionsCompletion);
            await chartCompletion.render();

            chartOntime = new ApexCharts(document.querySelector('#chart-personal-ontime'), optionsOntime);
            await chartOntime.render();

            chartTrend = new ApexCharts(document.querySelector('#chart-personal-trend'), optionsTrend);
            await chartTrend.render();
        };

        // Toggle Views Logic
        const viewSdlc = document.getElementById('view-sdlc');
        const viewRates = document.getElementById('view-rates');
        const viewTrend = document.getElementById('view-trend');

        const switchView = (mode) => {
            if (viewSdlc) viewSdlc.classList.toggle('d-none', mode !== 'sdlc');
            if (viewRates) viewRates.classList.toggle('d-none', mode !== 'rates');
            if (viewTrend) viewTrend.classList.toggle('d-none', mode !== 'trend');

            window.setTimeout(() => {
                if (mode === 'sdlc') {
                    if (chartSdlc) chartSdlc.resize();
                } else if (mode === 'rates') {
                    if (chartCompletion) chartCompletion.resize();
                    if (chartOntime) chartOntime.resize();
                } else if (mode === 'trend') {
                    if (chartTrend) chartTrend.resize();
                }
            }, 50);
        };

        const personalToggle = document.getElementById('personalChartToggle');
        if (personalToggle) {
            personalToggle.addEventListener('change', function() {
                switchView(this.value);
            });
        }

        const toggleDark = document.getElementById('toggle-dark');
        if (toggleDark) {
            toggleDark.addEventListener('change', function() {
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    rebuildPersonalCharts();
                }));
            });
        }

        // Post-Login Windowed Deadline Alert Modal Trigger
        <?php if (!empty($deadlineAlerts) && session()->getFlashdata('just_logged_in')) : ?>
            const deadlineModalEl = document.getElementById('modalWindowedDeadlineAlert');
            if (deadlineModalEl && typeof bootstrap !== 'undefined') {
                setTimeout(() => {
                    const modalInstance = new bootstrap.Modal(deadlineModalEl);
                    modalInstance.show();
                }, 400);
            }
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>