<?php

/** 
 * View: Macro Team Performance Dashboard
 * @var array<string, mixed> $metrics
 * @var string $selectedStartDate
 * @var string $selectedEndDate
 * @var \CodeIgniter\Pager\Pager|null $pager
 * @var int|null $totalMembers
 * @var int|null $totalFilteredMembers
 * @var string|null $memberKeyword
 */

$stats = $metrics['stats'] ?? [];
$members = $metrics['members'] ?? [];
$projects = $metrics['projects'] ?? [];
$capacity = $metrics['capacity'] ?? [];
$sdlcDistribution = $metrics['sdlc_distribution'] ?? [];
$completionChart = $metrics['chart'] ?? [];

$totalProjects = count($projects);
$activeProjects = (int) ($stats['active_projects'] ?? 0);
$totalCompleted = (int) ($stats['total_completed'] ?? 0);
$onTimeDone = (int) ($stats['on_time_done'] ?? 0);
$lateDone = (int) ($stats['late_done'] ?? 0);
$overdue = (int) ($stats['overdue'] ?? 0);
$riskUrgent = (int) ($stats['risk_urgent'] ?? 0);

$onTimeRate = $totalCompleted > 0 ? round(($onTimeDone / $totalCompleted) * 100, 1) : 0;
$totalMembers = (int) ($totalMembers ?? ($capacity['total_members'] ?? count($members)));
$totalFilteredMembers = (int) ($totalFilteredMembers ?? count($members));
$memberKeyword = (string) ($memberKeyword ?? '');
$avgActiveTasks = (float) ($capacity['avg_active_per_member'] ?? 0);
$organicCount = (int) ($capacity['organic_count'] ?? 0);
$nonOrganicCount = (int) ($capacity['non_organic_count'] ?? 0);
$organicActiveTasks = (int) ($capacity['organic_active_tasks'] ?? 0);
$nonOrganicActiveTasks = (int) ($capacity['non_organic_active_tasks'] ?? 0);
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    .flatpickr-input[readonly] {
        background-color: var(--bs-body-bg);
        cursor: pointer;
    }

    [data-bs-theme="dark"] .flatpickr-calendar,
    [data-bs-theme="dark"] .flatpickr-months .flatpickr-month,
    [data-bs-theme="dark"] .flatpickr-weekdays,
    [data-bs-theme="dark"] span.flatpickr-weekday {
        background: #1e1e2d;
        color: #f5f7ff;
    }

    [data-bs-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months,
    [data-bs-theme="dark"] .flatpickr-current-month input.cur-year,
    [data-bs-theme="dark"] .flatpickr-day {
        color: #e6eaee;
    }

    [data-bs-theme="dark"] .flatpickr-day:hover,
    [data-bs-theme="dark"] .flatpickr-day:focus {
        background: #2b2b40;
        border-color: #2b2b40;
    }

    [data-bs-theme="dark"] .flatpickr-day.selected {
        background: #435ebe;
        border-color: #435ebe;
        color: #ffffff;
    }

    .workload-pagination .pagination {
        margin: 0;
        gap: .35rem;
    }

    .workload-pagination .page-item .page-link {
        border: 0;
        border-radius: .55rem;
        min-width: 2.25rem;
        text-align: center;
        color: #52606d;
        font-weight: 600;
    }

    .workload-pagination .page-item.active .page-link {
        background: #435ebe;
        color: #fff;
        box-shadow: 0 .25rem .65rem rgba(67, 94, 190, .25);
    }

    .workload-pagination .page-item:not(.active) .page-link:hover {
        background: #eef1ff;
        color: #435ebe;
    }

    .workload-pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background: #f1f3f5;
        opacity: .75;
        cursor: not-allowed;
        pointer-events: none;
    }

    [data-bs-theme="dark"] .workload-pagination .page-item:not(.active) .page-link {
        color: #a0aec0;
        background: transparent;
    }

    [data-bs-theme="dark"] .workload-pagination .page-item:not(.active) .page-link:hover {
        background: rgba(67, 94, 190, 0.2);
        color: #8fa0f0;
    }

    [data-bs-theme="dark"] .workload-pagination .page-item.disabled .page-link {
        color: #607080;
        background: rgba(255, 255, 255, 0.05);
    }

    .kpi-macro-card .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .kpi-macro-card .kpi-sub {
        font-size: 0.76rem;
    }

    .team-member-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
    }

    .workload-matrix-table td,
    .workload-matrix-table th {
        vertical-align: middle;
    }

    .overdue-row {
        background-color: rgba(220, 53, 69, 0.045);
    }
</style>

<div class="page-heading">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
        <div>
            <h3>Kinerja Tim</h3>
            <p class="text-muted mb-0">Makro analitik & pengawasan performa departemen secara menyeluruh.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light-primary text-primary px-3 py-2 fs-7 fw-semibold">
                <i class="bi bi-people-fill me-1"></i><?= $totalMembers ?> Anggota Tim Aktif
            </span>
        </div>
    </div>
</div>

<!-- Filter Periode (Date Range & Quarterly Shortcuts) -->
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" id="filterForm" class="row g-2 align-items-end">
            <?php if (!empty($memberKeyword)) : ?>
                <input type="hidden" name="member_keyword" value="<?= esc($memberKeyword) ?>">
            <?php endif; ?>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small fw-semibold text-muted mb-1">Tanggal Mulai</label>
                <input type="text" name="filter_start" id="filterStart" class="form-control form-control-sm" placeholder="Pilih tanggal..." value="<?= esc($selectedStartDate ?? '') ?>">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small fw-semibold text-muted mb-1">Tanggal Selesai</label>
                <input type="text" name="filter_end" id="filterEnd" class="form-control form-control-sm" placeholder="Pilih tanggal..." value="<?= esc($selectedEndDate ?? '') ?>">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small fw-semibold text-muted mb-1">Kuartal (<?= date('Y') ?>)</label>
                <div class="btn-group btn-group-sm w-100" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-quarter" data-quarter="1">Q1</button>
                    <button type="button" class="btn btn-outline-secondary btn-quarter" data-quarter="2">Q2</button>
                    <button type="button" class="btn btn-outline-secondary btn-quarter" data-quarter="3">Q3</button>
                    <button type="button" class="btn btn-outline-secondary btn-quarter" data-quarter="4">Q4</button>
                </div>
            </div>
            <div class="col-12 col-md-12 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Terapkan Filter
                </button>
                <a href="<?= base_url('/kinerja-tim') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- 4 Macro KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Total Proyek -->
    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100 kpi-macro-card">
            <div class="card-body p-3">
                <small class="text-muted d-block mb-1">Total Portofolio Proyek</small>
                <span class="kpi-value text-primary"><?= $totalProjects ?></span>
                <small class="text-muted d-block kpi-sub mt-1">
                    <?= $activeProjects ?> aktif &middot; <?= $totalCompleted ?> selesai
                </small>
            </div>
        </div>
    </div>

    <!-- Proyek Aktif & Risiko -->
    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100 kpi-macro-card">
            <div class="card-body p-3">
                <small class="text-muted d-block mb-1">Proyek Dalam Pengerjaan</small>
                <span class="kpi-value text-info"><?= $activeProjects ?></span>
                <small class="d-block kpi-sub mt-1">
                    <?php if ($overdue > 0) : ?>
                        <span class="text-danger fw-semibold"><i class="bi bi-exclamation-octagon-fill me-1"></i><?= $overdue ?> Overdue</span>
                        <?= $riskUrgent > 0 ? "&middot; <span class=\"text-warning fw-semibold\">{$riskUrgent} Berisiko</span>" : '' ?>
                    <?php elseif ($riskUrgent > 0) : ?>
                        <span class="text-warning fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= $riskUrgent ?> Berisiko</span>
                    <?php else : ?>
                        <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Semua On Track</span>
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>

    <!-- On-Time Rate -->
    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100 kpi-macro-card">
            <div class="card-body p-3">
                <small class="text-muted d-block mb-1">Tingkat Ketepatan Waktu</small>
                <span class="kpi-value <?= $onTimeRate >= 70 ? 'text-success' : ($onTimeRate >= 40 ? 'text-warning' : 'text-danger') ?>">
                    <?= $onTimeRate ?>%
                </span>
                <small class="text-muted d-block kpi-sub mt-1">
                    <?= $onTimeDone ?> tepat waktu &middot; <?= $lateDone ?> terlambat
                </small>
            </div>
        </div>
    </div>

    <!-- Kapasitas & Beban Tim -->
    <div class="col-6 col-xl-3">
        <div class="card shadow-sm h-100 kpi-macro-card">
            <div class="card-body p-3">
                <small class="text-muted d-block mb-1">Rata-Rata Beban per Anggota</small>
                <span class="kpi-value text-dark"><?= $avgActiveTasks ?></span>
                <small class="text-muted d-block kpi-sub mt-1">
                    <?= $organicCount ?> Organik &middot; <?= $nonOrganicCount ?> Manmonth
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Macro Charts Grid -->
<div class="row g-4 mb-4">
    <!-- Left: SDLC Bottleneck Distribution -->
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fs-6 fw-bold">
                    <i class="bi bi-diagram-3 me-2 text-primary"></i>Distribusi Fase SDLC (Bottleneck Analysis)
                </h5>
                <span class="badge bg-light-info text-info border border-info-subtle">
                    <?= $activeProjects ?> Proyek Aktif
                </span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-6">
                        <?php if (!empty($sdlcDistribution)) : ?>
                            <div id="teamSdlcChart"></div>
                        <?php else : ?>
                            <div class="d-flex flex-column align-items-center justify-content-center text-center p-4 border rounded bg-light-subtle h-100" style="min-height: 220px;">
                                <small class="text-muted" style="font-size: 0.75rem;">Tidak ada proyek aktif pada periode filter ini.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12 col-md-6 border-start-md">
                        <small class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.72rem;">
                            Konsentrasi Tahapan Proyek
                        </small>
                        <?php if (!empty($sdlcDistribution)) : ?>
                            <div class="d-flex flex-column gap-2" style="max-height: 220px; overflow-y: auto;">
                                <?php
                                $totalActive = max(1, $activeProjects);
                                foreach ($sdlcDistribution as $phaseName => $count) :
                                    $pct = round(($count / $totalActive) * 100, 1);
                                ?>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold text-dark text-sm"><?= esc($phaseName) ?></span>
                                            <small class="text-muted text-xs">
                                                <strong class="text-primary"><?= (int) $count ?></strong> (<?= $pct ?>%)
                                            </small>
                                        </div>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="text-center py-4 text-muted">
                                <small>Tidak ada data proyek aktif pada periode ini.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Capacity Allocation & Monthly Completion Trend -->
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fs-6 fw-bold">
                    <i class="bi bi-bar-chart-line me-2 text-primary"></i>Tren & Alokasi Sumber Daya
                </h5>
                <select id="macroChartToggle" class="form-select form-select-sm w-auto py-1">
                    <option value="trend" selected>Tren Penyelesaian Bulanan</option>
                    <option value="capacity">Alokasi Kapasitas (Organik vs Manmonth)</option>
                </select>
            </div>
            <div class="card-body p-3">
                <!-- View 1: Monthly Completion Trend -->
                <div id="view-macro-trend">
                    <div class="text-center mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">
                            Tren Penyelesaian Proyek Departemen (6 Bulan Terakhir)
                        </small>
                    </div>
                    <div id="teamTrendChart"></div>
                </div>

                <!-- View 2: Capacity Allocation -->
                <div id="view-macro-capacity" class="d-none">
                    <div class="text-center mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">
                            Beban Tugas Aktif: Organik vs Non-Organik (Manmonth)
                        </small>
                    </div>
                    <?php if (($organicActiveTasks + $nonOrganicActiveTasks) > 0) : ?>
                        <div id="teamCapacityChart"></div>
                    <?php else : ?>
                        <div class="d-flex flex-column align-items-center justify-content-center text-center p-4 border rounded bg-light-subtle my-2" style="min-height: 190px;">
                            <i class="bi bi-people text-muted fs-3 mb-1"></i>
                            <div class="fw-semibold text-muted" style="font-size: 0.85rem;">Belum Ada Tugas Aktif</div>
                            <small class="text-muted" style="font-size: 0.75rem;">Tidak ada alokasi tugas aktif pada periode ini.</small>
                        </div>
                    <?php endif; ?>
                    <div class="row g-2 mt-2 pt-2 border-top text-center">
                        <div class="col-6">
                            <small class="text-muted d-block text-xs">Organik</small>
                            <strong class="text-primary"><?= $organicActiveTasks ?> Tugas</strong>
                            <small class="text-muted d-block text-xs">(<?= $organicCount ?> personel)</small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block text-xs">Manmonth</small>
                            <strong class="text-info"><?= $nonOrganicActiveTasks ?> Tugas</strong>
                            <small class="text-muted d-block text-xs">(<?= $nonOrganicCount ?> personel)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Team Workload Matrix Table -->
<div class="card shadow-sm workload-matrix-table mb-4">
    <div class="card-header py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="card-title mb-0 fs-6 fw-bold">
                <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Matriks Beban Kerja & Kapasitas Tim
            </h5>
            <small class="text-muted">Distribusi beban kerja seluruh staf dan pengembang pada departemen.</small>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="get" action="<?= base_url('/kinerja-tim') ?>" class="d-flex align-items-center">
                <?php if (!empty($selectedStartDate)) : ?>
                    <input type="hidden" name="filter_start" value="<?= esc($selectedStartDate) ?>">
                <?php endif; ?>
                <?php if (!empty($selectedEndDate)) : ?>
                    <input type="hidden" name="filter_end" value="<?= esc($selectedEndDate) ?>">
                <?php endif; ?>
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="member_keyword" class="form-control" placeholder="Cari nama personel..." value="<?= esc($memberKeyword) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </button>
                    <?php if (!empty($memberKeyword)) : ?>
                        <?php
                        $clearSearchQuery = [];
                        if (!empty($selectedStartDate)) $clearSearchQuery['filter_start'] = $selectedStartDate;
                        if (!empty($selectedEndDate)) $clearSearchQuery['filter_end'] = $selectedEndDate;
                        $clearSearchUrl = base_url('/kinerja-tim') . (!empty($clearSearchQuery) ? '?' . http_build_query($clearSearchQuery) : '');
                        ?>
                        <a href="<?= $clearSearchUrl ?>" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
            <span class="badge bg-light-primary text-primary border border-primary-subtle px-3 py-2">
                <?= (int) $totalFilteredMembers ?> Personel<?= !empty($memberKeyword) ? ' Ditemukan' : ' Terdaftar' ?>
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 28%;">Personel</th>
                        <th style="width: 18%;">Job Title</th>
                        <th class="text-center" style="width: 12%;">Tugas Aktif</th>
                        <th class="text-center" style="width: 12%;">Selesai</th>
                        <th class="text-center" style="width: 14%;">Status Beban</th>
                        <th class="text-end pe-4" style="width: 16%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($members)) : ?>
                        <?php foreach ($members as $member) : ?>
                            <?php
                            $isOverdue = (int) ($member['overdue'] ?? 0) > 0;
                            $wl = $member['workload_status'] ?? ['label' => 'Optimal', 'class' => 'success'];
                            ?>
                            <tr class="<?= $isOverdue ? 'overdue-row' : '' ?>">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="team-member-avatar bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold fs-6 flex-shrink-0">
                                            <?= esc(strtoupper(substr($member['name'] ?? 'U', 0, 1))) ?>
                                        </div>
                                        <div class="min-width-0">
                                            <strong class="text-dark d-block text-truncate"><?= esc($member['name']) ?></strong>
                                            <div class="d-flex align-items-center gap-1 mt-0">
                                                <span class="badge bg-light-secondary text-muted" style="font-size: 0.68rem;"><?= esc($member['role']) ?></span>
                                                <span class="badge bg-light-<?= ($member['category'] ?? '') === 'NonOrganik' ? 'warning text-dark' : 'info text-info' ?>" style="font-size: 0.68rem;"><?= esc($member['category'] ?? 'Organik') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-dark text-sm"><?= esc($member['job']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-primary text-primary fw-bold fs-7">
                                        <?= (int) $member['active_tasks'] ?>
                                    </span>
                                    <?php if ($isOverdue) : ?>
                                        <small class="d-block text-danger fw-semibold mt-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-clock-history me-1"></i><?= (int) $member['overdue'] ?> overdue
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="text-success fw-bold"><?= (int) $member['completed'] ?></span>
                                    <?php if ((int) ($member['late'] ?? 0) > 0) : ?>
                                        <small class="d-block text-danger" style="font-size: 0.7rem;">
                                            (<?= (int) $member['late'] ?> terlambat)
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-<?= esc($wl['class']) ?> text-<?= esc($wl['class']) ?> border border-<?= esc($wl['class']) ?>-subtle px-2 py-1" style="font-size: 0.75rem;">
                                        <?= esc($wl['label']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?= base_url('/users/detail/' . (int) $member['id']) ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="Lihat Analisis Kinerja Personal">
                                        <i class="bi bi-person-lines-fill me-1"></i> Detail Kinerja
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <?php if (!empty($memberKeyword)) : ?>
                                    <div>Tidak ditemukan personel dengan kata kunci "<strong><?= esc($memberKeyword) ?></strong>".</div>
                                    <div class="mt-2">
                                        <?php
                                        $clearSearchQuery = [];
                                        if (!empty($selectedStartDate)) $clearSearchQuery['filter_start'] = $selectedStartDate;
                                        if (!empty($selectedEndDate)) $clearSearchQuery['filter_end'] = $selectedEndDate;
                                        $clearSearchUrl = base_url('/kinerja-tim') . (!empty($clearSearchQuery) ? '?' . http_build_query($clearSearchQuery) : '');
                                        ?>
                                        <a href="<?= $clearSearchUrl ?>" class="btn btn-sm btn-outline-secondary">
                                            Hapus Pencarian
                                        </a>
                                    </div>
                                <?php else : ?>
                                    Belum ada data anggota tim pada sistem.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($members) && !empty($pager) && $pager->getPageCount('members') > 1) : ?>
            <div class="workload-pagination d-flex justify-content-end p-3 border-top">
                <?= $pager->links('members', 'complete') ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Executive Summary Alert -->
<div class="alert alert-light-primary border border-primary-subtle d-flex align-items-center gap-3 mb-0">
    <div class="fs-3 text-primary flex-shrink-0">
        <i class="bi bi-info-circle-fill"></i>
    </div>
    <div>
        <strong class="text-dark d-block">Ringkasan Eksekutif Departemen:</strong>
        <span class="text-muted small">
            Departemen saat ini mengelola <strong><?= $totalProjects ?></strong> proyek dengan <strong><?= $activeProjects ?></strong> proyek aktif dan <strong><?= $totalCompleted ?></strong> proyek selesai. Tingkat ketepatan waktu berada pada <strong><?= $onTimeRate ?>%</strong>. Beban rata-rata personel adalah <strong><?= $avgActiveTasks ?></strong> tugas per anggota tim.
            <?php if ($overdue > 0) : ?>
                Terdapat <strong class="text-danger"><?= $overdue ?> proyek overdue</strong> yang memerlukan koordinasi tindak lanjut.
            <?php endif; ?>
        </span>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
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

        const sdlcData = <?= json_encode($sdlcDistribution ?? []) ?>;
        const sdlcLabels = Object.keys(sdlcData);
        const sdlcSeries = Object.values(sdlcData).map(Number);
        const hasActiveSdlcData = sdlcSeries.some(val => val > 0);

        const trendMonths = <?= json_encode($completionChart['months'] ?? []) ?>;
        const trendOnTime = <?= json_encode($completionChart['on_time'] ?? []) ?>;
        const trendLate = <?= json_encode($completionChart['late'] ?? []) ?>;

        const organicActive = <?= (int) $organicActiveTasks ?>;
        const nonOrganicActive = <?= (int) $nonOrganicActiveTasks ?>;

        let chartSdlc = null;
        let chartTrend = null;
        let chartCapacity = null;

        const renderTeamCharts = async () => {
            if (chartSdlc) {
                chartSdlc.destroy();
                chartSdlc = null;
            }
            if (chartTrend) {
                chartTrend.destroy();
                chartTrend = null;
            }
            if (chartCapacity) {
                chartCapacity.destroy();
                chartCapacity = null;
            }

            const sdlcChartEl = document.querySelector("#teamSdlcChart");
            if (sdlcChartEl) sdlcChartEl.innerHTML = '';
            const trendChartEl = document.querySelector("#teamTrendChart");
            if (trendChartEl) trendChartEl.innerHTML = '';
            const capacityChartEl = document.querySelector("#teamCapacityChart");
            if (capacityChartEl) capacityChartEl.innerHTML = '';

            const themeOpts = getChartThemeOptions();

            // 1. SDLC Donut Chart
            if (hasActiveSdlcData && sdlcChartEl) {
                chartSdlc = new ApexCharts(sdlcChartEl, {
                    chart: {
                        type: 'donut',
                        height: 230,
                        ...themeOpts.chart
                    },
                    series: sdlcSeries,
                    labels: sdlcLabels,
                    colors: ['#435ebe', '#57caeb', '#5ddab4', '#ff7976', '#ffc107', '#6c757d'],
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

            // 2. Monthly Trend Stacked Bar Chart
            if (trendChartEl) {
                chartTrend = new ApexCharts(trendChartEl, {
                    chart: {
                        type: 'bar',
                        height: 230,
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
                            columnWidth: '45%',
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

            // 3. Capacity Allocation Donut Chart
            if ((organicActive + nonOrganicActive) > 0 && capacityChartEl) {
                chartCapacity = new ApexCharts(capacityChartEl, {
                    chart: {
                        type: 'donut',
                        height: 200,
                        ...themeOpts.chart
                    },
                    series: [organicActive, nonOrganicActive],
                    labels: ['Organik', 'Manmonth'],
                    colors: ['#435ebe', '#57caeb'],
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
                await chartCapacity.render();
            }
        };

        renderTeamCharts();

        const toggleDark = document.getElementById('toggle-dark');
        if (toggleDark) {
            toggleDark.addEventListener('change', function() {
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    renderTeamCharts();
                }));
            });
        }

        // Chart Toggle between Monthly Trend and Capacity
        const macroToggle = document.getElementById('macroChartToggle');
        if (macroToggle) {
            macroToggle.addEventListener('change', function() {
                const isTrend = this.value === 'trend';
                document.getElementById('view-macro-trend').classList.toggle('d-none', !isTrend);
                document.getElementById('view-macro-capacity').classList.toggle('d-none', isTrend);
                window.setTimeout(() => {
                    if (isTrend && chartTrend) chartTrend.resize();
                    if (!isTrend && chartCapacity) chartCapacity.resize();
                }, 50);
            });
        }

        // Flatpickr Range Initialization for Indonesian d/m/Y format
        let fpStart = null;
        let fpEnd = null;

        if (typeof flatpickr !== 'undefined') {
            const fpConfig = {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: false,
                locale: (flatpickr.l10ns && flatpickr.l10ns.id) ? flatpickr.l10ns.id : 'default',
            };

            const startEl = document.getElementById('filterStart');
            const endEl = document.getElementById('filterEnd');

            if (startEl) {
                fpStart = flatpickr(startEl, {
                    ...fpConfig,
                    onChange: function(selectedDates) {
                        if (fpEnd && selectedDates[0]) {
                            fpEnd.set('minDate', selectedDates[0]);
                            if (fpEnd.selectedDates[0] && fpEnd.selectedDates[0] < selectedDates[0]) {
                                fpEnd.setDate(selectedDates[0]);
                            }
                        }
                    }
                });
            }

            if (endEl) {
                fpEnd = flatpickr(endEl, {
                    ...fpConfig,
                    minDate: startEl && startEl.value ? startEl.value : null,
                });
            }
        }

        // Quarterly Filter Shortcuts
        const currentYear = new Date().getFullYear();
        const quarters = {
            1: {
                start: `${currentYear}-01-01`,
                end: `${currentYear}-03-31`
            },
            2: {
                start: `${currentYear}-04-01`,
                end: `${currentYear}-06-30`
            },
            3: {
                start: `${currentYear}-07-01`,
                end: `${currentYear}-09-30`
            },
            4: {
                start: `${currentYear}-10-01`,
                end: `${currentYear}-12-31`
            }
        };

        document.querySelectorAll('.btn-quarter').forEach(function(button) {
            button.addEventListener('click', function() {
                const q = this.getAttribute('data-quarter');
                if (quarters[q]) {
                    if (fpStart && fpEnd) {
                        fpStart.setDate(quarters[q].start, false);
                        fpEnd.set('minDate', quarters[q].start);
                        fpEnd.setDate(quarters[q].end, false);
                    } else {
                        document.getElementById('filterStart').value = quarters[q].start;
                        document.getElementById('filterEnd').value = quarters[q].end;
                    }
                    document.getElementById('filterForm').submit();
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>