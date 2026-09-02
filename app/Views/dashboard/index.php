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
                                <div class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3">
                                    <div class="min-width-0">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-light-<?= esc($alertItem['deadline_class']) ?> text-<?= esc($alertItem['deadline_class']) ?> fw-bold"><?= esc($alertItem['deadline_label']) ?></span>
                                            <strong class="text-dark text-truncate"><?= esc($alertItem['name']) ?></strong>
                                        </div>
                                        <div class="text-muted small">
                                            <span><i class="bi bi-tag me-1"></i><?= esc($alertItem['project_code']) ?></span> &middot;
                                            <span><i class="bi bi-calendar-event me-1"></i>Tenggat: <strong class="text-dark"><?= !empty($alertItem['end_date']) ? date('d M Y', strtotime($alertItem['end_date'])) : '-' ?></strong></span>
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
                        <?php if (!empty($user_detail['email'])) : ?>
                            <span><i class="bi bi-envelope me-1 text-primary"></i><?= esc($user_detail['email']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($user_detail['phone_number'])) : ?>
                            <span><i class="bi bi-telephone me-1 text-primary"></i><?= esc($user_detail['phone_number']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Counters -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <small class="text-muted d-block">Project Aktif</small>
                    <h3 class="text-info mb-0"><?= (int) $my_stats['active_projects'] ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <small class="text-muted d-block">Aplikasi Dikelola (PIC)</small>
                    <h3 class="text-primary mb-0"><?= (int) ($my_stats['total_apps_managed'] ?? 0) ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <small class="text-muted d-block">Total Selesai</small>
                    <h3 class="text-success mb-0"><?= (int) $my_stats['total_completed'] ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3">
                    <small class="text-muted d-block">Completion Rate</small>
                    <h3 class="text-warning mb-0"><?= number_format((float) ($my_stats['completion_rate'] ?? 0), 1) ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Work Area -->
    <?php
    $priority_projects = array_filter($my_active_projects, function ($p) {
        return in_array($p['deadline_label'] ?? '', ['Risk', 'Urgent', 'Critical', 'Overdue']);
    });
    ?>
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
                    <?php if (count($priority_projects) > 0) : ?>
                        <span class="badge bg-light-danger text-danger border border-danger-subtle py-1 px-2 fw-semibold">
                            <?= count($priority_projects) ?> Perlu Perhatian
                        </span>
                    <?php else : ?>
                        <span class="badge bg-light-success text-success border border-success-subtle py-1 px-2 fw-semibold">
                            Semua Aman
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 dashboard-project-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-2" style="width: 40%;">Project Name</th>
                                    <th class="py-2" style="width: 25%;">Status & Deadline</th>
                                    <th class="py-2" style="width: 23%;">Tenggat</th>
                                    <th class="text-center pe-3 py-2" style="width: 12%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($priority_projects)): ?>
                                    <?php foreach (array_slice($priority_projects, 0, 4) as $prj) : ?>
                                        <tr>
                                            <td class="ps-3 py-2">
                                                <strong class="d-block text-dark text-truncate" style="max-width: 220px;" title="<?= esc($prj['name']) ?>"><?= esc($prj['name']) ?></strong>
                                                <small class="text-muted"><?= esc($prj['project_code']) ?></small>
                                            </td>
                                            <td class="py-2">
                                                <span class="badge <?= esc($prj['status_class']) ?>"><?= esc($prj['status']) ?></span>
                                                <span class="badge bg-light-<?= esc($prj['deadline_class']) ?> text-<?= esc($prj['deadline_class']) ?> d-block mt-1" style="width: fit-content; font-size: 0.72rem;">
                                                    <?= esc($prj['deadline_label']) ?>
                                                </span>
                                            </td>
                                            <td class="py-2">
                                                <small class="d-block text-muted"><i class="bi bi-calendar-event me-1"></i><?= date('d M Y', strtotime($prj['end_date'])) ?></small>
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
                                            <i class="bi bi-check-circle-fill text-success me-1"></i> Bagus! Tidak ada project berisiko atau overdue saat ini.
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
                        <small class="text-muted d-none d-sm-inline">| Distribusi & Timeline</small>
                    </div>
                    <select id="personalChartToggle" class="form-select form-select-sm w-auto py-1">
                        <option value="sdlc">Distribusi Fase SDLC</option>
                        <option value="timeline">Timeline Deadlines</option>
                    </select>
                </div>
                <div class="card-body p-3">
                    <div id="chart-personal-sdlc"></div>
                    <div id="chart-personal-timeline" class="d-none"></div>
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
                        <div class="d-flex flex-column gap-2">
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
                            <i class="bi bi-laptop fs-3 d-block mb-1 text-secondary opacity-50"></i>
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
                    <!-- Quick Actions Grid -->
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
                        <div class="col-6">
                            <a href="<?= base_url('/projects') ?>" class="btn btn-sm btn-secondary text-white w-100 d-flex align-items-center justify-content-center gap-2 py-2 text-nowrap">
                                <i class="bi bi-kanban lh-1"></i> <span class="fw-semibold">Tracker Project</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= base_url('/aplikasi') ?>" class="btn btn-sm btn-dark text-white w-100 d-flex align-items-center justify-content-center gap-2 py-2 text-nowrap">
                                <i class="bi bi-grid-3x3-gap lh-1"></i> <span class="fw-semibold">Katalog App</span>
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
                                                <div class="text-muted d-flex align-items-center justify-content-between flex-wrap gap-1" style="font-size: 0.74rem;">
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
        const timelineDataRaw = <?= json_encode($workload_timeline ?? []) ?>;

        const sdlcLabels = Object.keys(sdlcDataRaw);
        const sdlcSeries = Object.values(sdlcDataRaw);

        const ganttData = timelineDataRaw.map(p => {
            let color = '#435ebe';
            const statusClass = String(p.class || '');
            if (statusClass.includes('success')) color = '#198754';
            else if (statusClass.includes('warning')) color = '#ffc107';
            else if (statusClass.includes('info')) color = '#0dcaf0';
            else if (statusClass.includes('danger')) color = '#dc3545';

            return {
                x: String(p.name || '').length > 20 ? String(p.name || '').substring(0, 20) + '...' : String(p.name || ''),
                y: [new Date(p.start).getTime(), new Date(p.end).getTime()],
                fillColor: color
            };
        });

        var personalThemeOptions = getChartThemeOptions();

        // SDLC Chart (Donut)
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

        // Timeline Chart (Gantt / RangeBar)
        var chartTimeline = null;
        var optionsTimeline = {
            chart: {
                type: 'rangeBar',
                height: 240,
                toolbar: {
                    show: false
                },
                ...personalThemeOptions.chart
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4
                }
            },
            series: [{
                name: 'Timeline',
                data: ganttData.length > 0 ? ganttData : [{
                    x: 'No Active Projects',
                    y: [new Date().getTime(), new Date().getTime()]
                }]
            }],
            xaxis: {
                type: 'datetime',
                ...personalThemeOptions.xaxis
            },
            theme: personalThemeOptions.theme,
            tooltip: {
                ...personalThemeOptions.tooltip,
                x: {
                    format: 'dd MMM yyyy'
                }
            },
            grid: personalThemeOptions.grid,
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            yaxis: {
                ...personalThemeOptions.yaxis,
                labels: {
                    ...personalThemeOptions.yaxis.labels,
                    style: {
                        colors: personalThemeOptions.yaxis.labels.style.colors,
                        fontSize: '11px',
                        fontWeight: 600
                    }
                }
            }
        };

        const renderTimelineChart = async () => {
            if (chartTimeline) {
                return;
            }

            const timelineElement = document.querySelector('#chart-personal-timeline');
            if (!timelineElement) {
                return;
            }

            chartTimeline = new ApexCharts(timelineElement, optionsTimeline);
            await chartTimeline.render();
        };

        const rebuildPersonalCharts = async () => {
            const selectedChart = document.getElementById('personalChartToggle').value;
            if (chartSdlc) {
                chartSdlc.destroy();
                chartSdlc = null;
            }
            if (chartTimeline) {
                chartTimeline.destroy();
                chartTimeline = null;
            }
            document.querySelector('#chart-personal-sdlc').innerHTML = '';
            document.querySelector('#chart-personal-timeline').innerHTML = '';

            // Rebuild from fresh theme options so no stale palette remains after theme changes.
            const freshTheme = getChartThemeOptions();
            optionsSdlc.chart = {
                type: 'donut',
                height: 300,
                ...freshTheme.chart
            };
            optionsSdlc.theme = freshTheme.theme;
            optionsSdlc.tooltip = freshTheme.tooltip;
            optionsSdlc.legend = {
                position: 'bottom',
                ...freshTheme.legend
            };
            optionsSdlc.dataLabels = {
                enabled: true,
                ...freshTheme.teamDataLabels
            };
            optionsSdlc.plotOptions = freshTheme.plotOptions;
            optionsTimeline.chart = {
                type: 'rangeBar',
                height: 300,
                toolbar: {
                    show: false
                },
                ...freshTheme.chart
            };
            optionsTimeline.theme = freshTheme.theme;
            optionsTimeline.tooltip = {
                ...freshTheme.tooltip,
                x: {
                    format: 'dd MMM yyyy'
                }
            };
            optionsTimeline.grid = freshTheme.grid;
            optionsTimeline.xaxis = {
                type: 'datetime',
                ...freshTheme.xaxis
            };
            optionsTimeline.yaxis = {
                ...freshTheme.yaxis,
                labels: {
                    ...freshTheme.yaxis.labels,
                    style: {
                        colors: freshTheme.yaxis.labels.style.colors,
                        fontSize: '11px',
                        fontWeight: 600
                    }
                }
            };
            chartSdlc = new ApexCharts(document.querySelector('#chart-personal-sdlc'), optionsSdlc);
            await chartSdlc.render();

            if (selectedChart === 'timeline') {
                await renderTimelineChart();
                chartTimeline.resize();
            }
        };

        const sdlcContainer = document.getElementById('chart-personal-sdlc');
        const timelineContainer = document.getElementById('chart-personal-timeline');

        // Toggle visibility first, then resize the chart after the browser lays out its container.
        const showPersonalChart = (chartType) => {
            const showSdlc = chartType === 'sdlc';
            sdlcContainer.classList.toggle('d-none', !showSdlc);
            timelineContainer.classList.toggle('d-none', showSdlc);

            window.setTimeout(() => {
                if (showSdlc) {
                    chartSdlc.resize();
                } else {
                    renderTimelineChart().then(() => {
                        if (chartTimeline) {
                            chartTimeline.resize();
                        }
                    });
                }
            }, 50);
        };

        // Toggle Logic
        document.getElementById('personalChartToggle').addEventListener('change', function() {
            showPersonalChart(this.value);
        });

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