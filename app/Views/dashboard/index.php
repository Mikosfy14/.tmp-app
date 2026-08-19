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
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="page-heading">
    <h3>Dashboard</h3>
</div>

<div class="page-content">
    <div class="row mb-4">
        <div class="col-12 col-lg-7 mb-3 mb-lg-0">
            <div class="card bg-primary text-white shadow-sm h-100 mb-0">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-light-primary text-black border border-white px-2 py-1"><?= date('l, d F Y') ?></span>
                        </div>
                        <h2 class="text-white fw-bold mt-2 mb-1">
                            Selamat Datang, <?= esc(session()->get('name')) ?>!
                        </h2>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white-50 d-flex gap-4">
                        <div>
                            <small class="text-white-50 d-block">Status Akun</small>
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Aktif</span>
                        </div>
                        <div>
                            <small class="text-white-50 d-block">Akses Role</small>
                            <span class="fw-bold"><?= esc(session()->get('role_name')) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fs-6 fw-bold text-primary"><i class="bi bi-person-badge me-2"></i>Informasi Akun</h5>
                    <span class="badge bg-light-primary text-primary"><?= esc(session()->get('category')) ?? 'Organik' ?></span>
                </div>
                <div class="card-body pt-2">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="avatar avatar-lg bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 50px; height: 50px; border-radius: 50%;">
                            <?= strtoupper(substr(session()->get('name'), 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold"><?= esc(session()->get('name')) ?></h6>
                            <p class="text-muted text-sm mb-0"><?= esc($user_detail['job_title'] ?? 'Staff Developer') ?></p>
                        </div>
                    </div>
                    <div class="row g-2 text-sm">
                        <div class="col-6">
                            <span class="text-muted d-block"><i class="bi bi-person me-1"></i> Username:</span>
                            <strong class="text-dark"><?= esc(session()->get('username')) ?></strong>
                        </div>
                        <div class="col-6">
                            <div class="text-truncate">
                                <span class="text-muted d-block"><i class="bi bi-envelope me-1"></i> Email:</span>
                                <strong class="text-dark"><?= esc($user_detail['email'] ?? 'N/A') ?></strong>
                            </div>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block"><i class="bi bi-telephone me-1"></i> No. Telepon:</span>
                            <strong class="text-dark"><?= esc($user_detail['phone_number'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block"><i class="bi bi-shield-check me-1"></i> Role & Akses:</span>
                            <strong class="text-dark"><?= esc(session()->get('role_name')) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-journal-bookmark me-2 text-primary"></i>Dashboard Project Pribadi</h4>
        <span class="text-muted text-sm">Target dan kinerja Individu</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="stats-icon blue d-flex align-items-center justify-content-center flex-shrink-0">
                            <span class="fw-bold text-white fs-4 lh-1"><?= $my_stats['active_projects'] ?></span>
                        </div>
                        <div>
                            <h6 class="text fw-bold mb-0">Project Aktif</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 col-md-6">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="stats-icon green d-flex align-items-center justify-content-center flex-shrink-0">
                            <span class="fw-bold text-white fs-4 lh-1"><?= $my_stats['on_time_done'] ?></span>
                        </div>
                        <div>
                            <h6 class="text fw-bold mb-0">Project Selesai</h6>
                            <h6 class="text fw-bold mb-0">(Tepat Waktu)</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 col-md-6">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="stats-icon red d-flex align-items-center justify-content-center flex-shrink-0">
                            <span class="fw-bold text-white fs-4 lh-1"><?= $my_stats['late_done'] ?></span>
                        </div>
                        <div>
                            <h6 class="text fw-bold mb-0">Project Selesai (Terlambat)</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 col-md-6">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="stats-icon purple d-flex align-items-center justify-content-center flex-shrink-0">
                            <span class="fw-bold text-white fs-4 lh-1"><?= $my_stats['total_completed'] ?></span>
                        </div>
                        <div>
                            <h6 class="text fw-bold mb-0">Total Selesai</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-list-task me-2 text-primary"></i>Overview Project Saya</h5>
        <span class="badge bg-primary"><?= count($my_active_projects) ?> Project</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 dashboard-project-table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 30%;">Kode & Nama Project</th>
                        <th style="width: 14%;">Status</th>
                        <th style="width: 22%;">Assigned To</th>
                        <th style="width: 22%;">Timeline</th>
                        <th class="text-center pe-4" style="width: 12%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($my_active_projects)): ?>
                        <?php foreach ($my_active_projects as $prj) : ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <strong class="d-block text-dark"><?= esc($prj['name']) ?></strong>
                                    <span class="badge bg-light-secondary text-muted"><?= esc($prj['project_code']) ?></span>
                                </td>
                                <td class="py-3">
                                    <span class="badge <?= esc($prj['status_class']) ?>"><?= esc($prj['status']) ?></span>
                                    <span class="badge dashboard-deadline-badge bg-light-<?= esc($prj['deadline_class']) ?> text-<?= esc($prj['deadline_class']) ?> d-block mt-2" style="width: fit-content;">
                                        <?= esc($prj['deadline_label']) ?>
                                    </span>
                                </td>
                                <td class="py-3">
                                    <?php if (!empty($prj['assigned_users'])) : ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (array_slice($prj['assigned_users'], 0, 2) as $assignedUser) : ?>
                                                <span class="badge bg-light-primary text-primary" title="<?= esc($assignedUser['job_title'] ?? '') ?>">
                                                    <i class="bi bi-person-fill me-1"></i><?= esc($assignedUser['name']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (count($prj['assigned_users']) > 2) : ?>
                                                <span class="badge bg-light-secondary text-secondary">+<?= count($prj['assigned_users']) - 2 ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="text-muted text-sm">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <small class="d-block text-muted">Start: <span class="fw-bold text-dark"><?= date('d M Y', strtotime($prj['start_date'])) ?></span></small>
                                    <small class="d-block text-muted">End: <span class="fw-bold text-dark"><?= date('d M Y', strtotime($prj['end_date'])) ?></span></small>
                                    <small class="d-block text-muted">Promote: <span class="fw-bold text-dark"><?= !empty($prj['promote_date']) ? date('d M Y', strtotime($prj['promote_date'])) : '-' ?></span></small>
                                </td>
                                <td class="text-center pe-4 py-3">
                                    <a href="<?= base_url('/projects') ?>" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 text-nowrap" title="Lihat Project Tracker">
                                        <i class="bi bi-eye-fill"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">Belum ada project aktif yang ditangani.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .dashboard-project-table> :not(caption)>*>* {
        padding-left: 1.25rem !important;
        padding-right: 1.25rem !important;
    }
</style>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fs-6 fw-bold text-primary"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Grafik Penyelesaian Project</h5>
                <span class="badge bg-light-secondary text-dark">Tepat Waktu vs Terlambat</span>
            </div>
            <div class="card-body">
                <div id="chart-personal-performance"></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header pb-2">
                <h5 class="card-title mb-0 fs-6 fw-bold"></i>Timeline & Deadline Terdekat</h5>
            </div>
            <div class="card-body pt-3">
                <ul class="list-group list-group-flush">
                    <?php foreach ($my_timeline as $item) : ?>
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-0 text-dark text-sm fw-bold"><?= esc($item['title']) ?></h6>
                                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?= $item['date'] ?></small>
                            </div>
                            <span class="badge timeline-status-badge bg-light-<?= $item['class'] ?> text-<?= $item['class'] ?>"><?= $item['badge'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>


<?php if (session()->get('role_name') === 'Kepala Departemen') : ?>
    <hr class="my-5 border-2">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Dashboard Project Tim & Kinerja Departemen</h4>
            <p class="text-muted text-sm mb-0">Menu kepala departemen untuk memantau performa anggota tim</p>
        </div>
    </div>

    <div class="row">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <span class="text-muted text-sm font-semibold">Total Project</span>
                    <h3 class="team-stat-value fw-bold mb-0 text-primary"><?= $team_stats['total_team_projects'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <span class="text-muted text-sm font-semibold">Anggota Tim Aktif</span>
                    <h3 class="team-stat-value fw-bold mb-0 text-success"><?= $team_stats['active_members'] ?> Staff</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <span class="text-muted text-sm font-semibold">Project Overdue / Delay</span>
                    <h3 class="team-stat-value fw-bold mb-0 text-danger"><?= $team_stats['overdue_projects'] ?> Project</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <span class="text-muted text-sm font-semibold">Efisiensi Tim</span>
                    <h3 class="team-stat-value fw-bold mb-0 text-info"><?= $team_stats['efficiency_rate'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-5 mb-4">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-pie-chart me-2 text-primary"></i>Beban Kerja & Kinerja Tim</h5>
                </div>
                <div class="card-body">
                    <div id="chart-team-performance"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7 mb-4">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fs-6 fw-bold">
                        <i class="bi bi-person-lines-fill me-2 text-primary"></i>Daftar Anggota Tim & Project Tracker
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 custom-table-team">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 28%;">Anggota</th>
                                    <th style="width: 24%;">Job Title</th>
                                    <th class="text-center" style="width: 14%;">Project Aktif</th>
                                    <th class="text-center" style="width: 14%;">Project Selesai</th>
                                    <th class="text-center pe-4" style="width: 20%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($team_members as $member) : ?>
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <strong class="d-block text-dark mb-1"><?= esc($member['name']) ?></strong>
                                            <span class="badge bg-light-secondary text-secondary fw-semibold"><?= esc($member['role']) ?></span>
                                        </td>
                                        <td class="py-3">
                                            <span class="text-secondary fw-medium"><?= esc($member['job']) ?></span>
                                        </td>
                                        <td class="text-center py-3">
                                            <span class="fw-bold text-primary fs-6"><?= $member['active_tasks'] ?></span>
                                        </td>
                                        <td class="text-center py-3">
                                            <span class="fw-bold text-success fs-6"><?= $member['completed'] ?></span>
                                        </td>
                                        <td class="text-center pe-4 py-3">
                                            <a href="<?= base_url('/dashboard/user/' . $member['id']) ?>" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 text-nowrap">
                                                <i class="bi bi-speedometer2"></i> Dashboard Staff
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Mengatur padding horizontal agar tabel terasa renggang & rapi */
            .custom-table-team> :not(caption)>*>* {
                padding-left: 1.1rem !important;
                padding-right: 1.1rem !important;
            }
        </style>
    </div>
<?php endif; ?>

</div>

<style>
    [data-bs-theme="dark"] .timeline-status-badge.bg-light-danger {
        background-color: #ffdede !important;
        color: #dc3545 !important;
    }

    [data-bs-theme="dark"] .timeline-status-badge.bg-light-info {
        background-color: #e6fdff !important;
        color: #0dcaf0 !important;
    }

    [data-bs-theme="dark"] .timeline-status-badge.bg-light-success {
        background-color: #d2ffe8 !important;
        color: #198754 !important;
    }

    [data-bs-theme="dark"] .timeline-status-badge.bg-light-warning {
        background-color: #fffdd8 !important;
        color: #ffc107 !important;
    }

    [data-bs-theme="dark"] .team-stat-value.text-primary {
        color: #435ebe !important;
    }

    [data-bs-theme="dark"] .team-stat-value.text-success {
        color: #198754 !important;
    }

    [data-bs-theme="dark"] .team-stat-value.text-danger {
        color: #dc3545 !important;
    }

    [data-bs-theme="dark"] .team-stat-value.text-info {
        color: #0dcaf0 !important;
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

        const completionChartData = <?= json_encode($completion_chart ?? [
            'months' => ['Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu'],
            'on_time' => [0, 0, 0, 0, 0, 0],
            'late' => [0, 0, 0, 0, 0, 0],
        ]) ?>;

        // 1. Chart Penyelesaian Project Personal
        var optionsPersonal = {
            chart: {
                type: 'bar',
                height: 280,
                stacked: true,
                toolbar: {
                    show: false
                }
            },
            series: [{
                name: 'Selesai Tepat Waktu',
                data: completionChartData.on_time
            }, {
                name: 'Selesai Terlambat',
                data: completionChartData.late
            }],
            colors: ['#198754', '#dc3545'],
            xaxis: {
                categories: completionChartData.months
            },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: {
                    formatter: function(value) {
                        return Math.round(value);
                    }
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '48%',
                    dataLabels: {
                        total: {
                            enabled: true,
                            style: {
                                fontSize: '12px',
                                fontWeight: 700
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'top'
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value + ' project';
                    }
                }
            }
        };
        var personalThemeOptions = getChartThemeOptions();
        optionsPersonal = {
            ...optionsPersonal,
            chart: {
                ...optionsPersonal.chart,
                ...personalThemeOptions.chart
            },
            theme: personalThemeOptions.theme,
            tooltip: {
                ...optionsPersonal.tooltip,
                ...personalThemeOptions.tooltip,
            },
            yaxis: {
                ...optionsPersonal.yaxis,
                labels: {
                    ...optionsPersonal.yaxis.labels,
                    ...personalThemeOptions.yaxis.labels
                }
            },
            grid: personalThemeOptions.grid,
            xaxis: {
                ...optionsPersonal.xaxis,
                ...personalThemeOptions.xaxis
            },
            dataLabels: {
                ...optionsPersonal.dataLabels,
                ...personalThemeOptions.dataLabels
            },
            legend: {
                ...optionsPersonal.legend,
                ...personalThemeOptions.legend
            },
            plotOptions: optionsPersonal.plotOptions
        };
        var chartPersonal = new ApexCharts(document.querySelector("#chart-personal-performance"), optionsPersonal);
        chartPersonal.render();

        // 2. Chart Kinerja Tim khusus Kadept (Donut Chart)
        <?php if (session()->get('role_name') === 'Kepala Departemen') : ?>
            var optionsTeam = {
                chart: {
                    type: 'donut',
                    height: 290
                },
                series: [12, 4, 2],
                labels: ['Project Selesai', 'Sedang Berjalan', 'Terlambat / Pending'],
                colors: ['#198754', '#435ebe', '#dc3545'],
                legend: {
                    position: 'bottom'
                }
            };
            var teamThemeOptions = getChartThemeOptions();
            optionsTeam = {
                ...optionsTeam,
                chart: {
                    ...optionsTeam.chart,
                    ...teamThemeOptions.chart
                },
                theme: teamThemeOptions.theme,
                tooltip: teamThemeOptions.tooltip,
                dataLabels: teamThemeOptions.teamDataLabels,
                plotOptions: teamThemeOptions.plotOptions,
                legend: {
                    ...optionsTeam.legend,
                    ...teamThemeOptions.legend
                }
            };
            var chartTeam = new ApexCharts(document.querySelector("#chart-team-performance"), optionsTeam);
            chartTeam.render();
        <?php endif; ?>

        const toggleDark = document.getElementById('toggle-dark');
        if (toggleDark) {
            toggleDark.addEventListener('change', function() {
                setTimeout(function() {
                    const themeOptions = getChartThemeOptions();

                    chartPersonal.updateOptions({
                        ...themeOptions,
                        xaxis: {
                            ...optionsPersonal.xaxis,
                            ...themeOptions.xaxis
                        },
                        yaxis: {
                            ...optionsPersonal.yaxis,
                            labels: {
                                ...optionsPersonal.yaxis.labels,
                                ...themeOptions.yaxis.labels
                            }
                        },
                        tooltip: {
                            ...optionsPersonal.tooltip,
                            ...themeOptions.tooltip
                        },
                        plotOptions: optionsPersonal.plotOptions
                    });

                    <?php if (session()->get('role_name') === 'Kepala Departemen') : ?>
                        chartTeam.updateOptions({
                            ...themeOptions,
                            dataLabels: themeOptions.teamDataLabels
                        });
                    <?php endif; ?>
                }, 0);
            });
        }
    });
</script>

<?= $this->endSection() ?>``
