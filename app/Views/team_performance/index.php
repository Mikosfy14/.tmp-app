<?php

/** 
 * View: Macro Team Performance Dashboard
 * @var array<string, mixed> $metrics
 * @var string $selectedStartDate
 * @var string $selectedEndDate
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
$totalMembers = (int) ($capacity['total_members'] ?? count($members));
$avgActiveTasks = (float) ($capacity['avg_active_per_member'] ?? 0);
$organicCount = (int) ($capacity['organic_count'] ?? 0);
$nonOrganicCount = (int) ($capacity['non_organic_count'] ?? 0);
$organicActiveTasks = (int) ($capacity['organic_active_tasks'] ?? 0);
$nonOrganicActiveTasks = (int) ($capacity['non_organic_active_tasks'] ?? 0);
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
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
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small fw-semibold text-muted mb-1">Tanggal Mulai</label>
                <input type="date" name="filter_start" id="filterStart" class="form-control form-control-sm" value="<?= esc($selectedStartDate ?? '') ?>">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small fw-semibold text-muted mb-1">Tanggal Selesai</label>
                <input type="date" name="filter_end" id="filterEnd" class="form-control form-control-sm" value="<?= esc($selectedEndDate ?? '') ?>">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small fw-semibold text-muted mb-1">Pintasan Kuartal (<?= date('Y') ?>)</label>
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
                        <div id="teamSdlcChart"></div>
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
                    <div id="teamCapacityChart"></div>
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
        <span class="badge bg-light-primary text-primary border border-primary-subtle px-3 py-2">
            <?= count($members) ?> Personel Terdaftar
        </span>
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
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data anggota tim pada sistem.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    function getChartThemeOptions() {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const textColor = isDark ? '#f5f7ff' : '#25396f';
        const mutedColor = isDark ? '#a6a8b8' : '#607080';
        const gridColor = isDark ? '#2b2b40' : '#e6eaee';
        return {
            chart: { foreColor: textColor },
            theme: { mode: isDark ? 'dark' : 'light' },
            tooltip: { theme: isDark ? 'dark' : 'light' },
            legend: { labels: { colors: textColor } },
            grid: { borderColor: gridColor },
            xaxis: { labels: { style: { colors: mutedColor } }, axisBorder: { color: gridColor }, axisTicks: { color: gridColor } },
            yaxis: { labels: { style: { colors: mutedColor } } }
        };
    }

    const themeOpts = getChartThemeOptions();

    // 1. SDLC Donut Chart
    const sdlcData = <?= json_encode($sdlcDistribution) ?>;
    const sdlcLabels = Object.keys(sdlcData);
    const sdlcSeries = Object.values(sdlcData);

    var chartSdlc = new ApexCharts(document.querySelector("#teamSdlcChart"), {
        chart: { type: 'donut', height: 230, ...themeOpts.chart },
        series: sdlcSeries.length > 0 ? sdlcSeries : [1],
        labels: sdlcSeries.length > 0 ? sdlcLabels : ['Tidak Ada Proyek Aktif'],
        colors: ['#435ebe', '#57caeb', '#5ddab4', '#ff7976', '#ffc107', '#6c757d'],
        theme: themeOpts.theme,
        tooltip: themeOpts.tooltip,
        legend: { position: 'bottom', fontSize: '11px', ...themeOpts.legend },
        dataLabels: { enabled: true, style: { colors: ['#ffffff'] } },
        plotOptions: { pie: { donut: { labels: { show: false } } } }
    });
    chartSdlc.render();

    // 2. Monthly Trend Stacked Bar Chart
    const trendMonths = <?= json_encode($completionChart['months'] ?? []) ?>;
    const trendOnTime = <?= json_encode($completionChart['on_time'] ?? []) ?>;
    const trendLate = <?= json_encode($completionChart['late'] ?? []) ?>;

    var chartTrend = new ApexCharts(document.querySelector("#teamTrendChart"), {
        chart: { type: 'bar', height: 230, stacked: true, toolbar: { show: false }, ...themeOpts.chart },
        series: [
            { name: 'Tepat Waktu', data: trendOnTime },
            { name: 'Terlambat', data: trendLate }
        ],
        colors: ['#198754', '#dc3545'],
        plotOptions: { bar: { horizontal: false, columnWidth: '45%', borderRadius: 3 } },
        xaxis: { categories: trendMonths, ...themeOpts.xaxis },
        yaxis: { ...themeOpts.yaxis, labels: { ...themeOpts.yaxis.labels, formatter: function(val) { return Math.floor(val); } } },
        grid: themeOpts.grid,
        legend: { position: 'top', fontSize: '11px', ...themeOpts.legend },
        tooltip: { ...themeOpts.tooltip, y: { formatter: function(val) { return val + ' project'; } } },
        dataLabels: { enabled: false },
        theme: themeOpts.theme
    });
    chartTrend.render();

    // 3. Capacity Allocation Donut Chart
    const organicActive = <?= $organicActiveTasks ?>;
    const nonOrganicActive = <?= $nonOrganicActiveTasks ?>;

    var chartCapacity = new ApexCharts(document.querySelector("#teamCapacityChart"), {
        chart: { type: 'donut', height: 200, ...themeOpts.chart },
        series: (organicActive + nonOrganicActive > 0) ? [organicActive, nonOrganicActive] : [1],
        labels: (organicActive + nonOrganicActive > 0) ? ['Organik', 'Manmonth'] : ['Belum Ada Tugas'],
        colors: ['#435ebe', '#57caeb'],
        theme: themeOpts.theme,
        tooltip: themeOpts.tooltip,
        legend: { position: 'bottom', fontSize: '11px', ...themeOpts.legend },
        dataLabels: { enabled: true, style: { colors: ['#ffffff'] } },
        plotOptions: { pie: { donut: { labels: { show: false } } } }
    });
    chartCapacity.render();

    // Chart Toggle between Monthly Trend and Capacity
    document.getElementById('macroChartToggle').addEventListener('change', function() {
        const isTrend = this.value === 'trend';
        document.getElementById('view-macro-trend').classList.toggle('d-none', !isTrend);
        document.getElementById('view-macro-capacity').classList.toggle('d-none', isTrend);
    });

    // Quarterly Filter Shortcuts
    const currentYear = new Date().getFullYear();
    const quarters = {
        1: { start: `${currentYear}-01-01`, end: `${currentYear}-03-31` },
        2: { start: `${currentYear}-04-01`, end: `${currentYear}-06-30` },
        3: { start: `${currentYear}-07-01`, end: `${currentYear}-09-30` },
        4: { start: `${currentYear}-10-01`, end: `${currentYear}-12-31` }
    };

    document.querySelectorAll('.btn-quarter').forEach(function(button) {
        button.addEventListener('click', function() {
            const q = this.getAttribute('data-quarter');
            if (quarters[q]) {
                document.getElementById('filterStart').value = quarters[q].start;
                document.getElementById('filterEnd').value = quarters[q].end;
                document.getElementById('filterForm').submit();
            }
        });
    });
});
</script>
<?= $this->endSection() ?>