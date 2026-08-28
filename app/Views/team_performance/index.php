<?php
/** @var array<string, mixed> $metrics */
$stats = $metrics['stats'] ?? [];
$members = $metrics['members'] ?? [];
$projects = $metrics['projects'] ?? [];
$statusCounts = [];
foreach ($projects as $project) {
    $status = (string) ($project['status'] ?? 'Tanpa Status');
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
}
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<style>
    .team-performance-chart-card .card-header { background: transparent; }
    .team-performance-chart-card #teamMemberChart { min-height: 340px; }
    .team-performance-table .overdue-row { background-color: rgba(220, 53, 69, .045); }
</style>
<div class="page-heading"><h3>Kinerja Tim</h3><p class="text-muted">Rekap performa project seluruh anggota tim berdasarkan data aktual.</p></div>
<div class="card shadow-sm mb-4"><div class="card-body p-3"><form method="get" class="row g-2 align-items-end"><div class="col-md-5"><label class="form-label">Tanggal Mulai</label><input type="date" name="filter_start" class="form-control" value="<?= esc($selectedStartDate ?? '') ?>"></div><div class="col-md-5"><label class="form-label">Tanggal Selesai</label><input type="date" name="filter_end" class="form-control" value="<?= esc($selectedEndDate ?? '') ?>"></div><div class="col-md-1"><button class="btn btn-primary w-100" title="Terapkan filter" aria-label="Terapkan filter"><i class="bi bi-search"></i></button></div><div class="col-md-1"><a href="<?= base_url('/kinerja-tim') ?>" class="btn btn-outline-secondary w-100" title="Reset filter" aria-label="Reset filter"><i class="bi bi-arrow-counterclockwise"></i></a></div></form></div></div>
<div class="row g-3 mb-4"><?php foreach ([['Total Project', count($projects), 'primary'], ['Project Aktif', $stats['active_projects'] ?? 0, 'info'], ['Project Selesai', $stats['total_completed'] ?? 0, 'success'], ['Overdue', $stats['overdue'] ?? 0, 'danger']] as [$label, $value, $color]) : ?><div class="col-6 col-xl-3"><div class="card shadow-sm h-100"><div class="card-body"><small class="text-muted"><?= $label ?></small><h3 class="text-<?= $color ?> mb-0"><?= (int) $value ?></h3></div></div></div><?php endforeach; ?></div>
<div class="row g-4 mb-4"><div class="col-xl-7"><div class="card shadow-sm h-100 team-performance-chart-card"><div class="card-header d-flex justify-content-between align-items-center gap-2"><div><h5 class="mb-1">Top 10 Performa Anggota</h5><small class="text-muted">Pilih satu metrik agar perbandingan tetap mudah dibaca.</small></div><select id="teamMetricSelect" class="form-select form-select-sm" style="max-width: 180px"><option value="completed">Project Selesai</option><option value="active_tasks">Project Aktif</option><option value="late">Terlambat</option><option value="overdue">Overdue</option><option value="completion_rate">Completion Rate</option></select></div><div class="card-body"><div id="teamMemberChart"></div></div></div></div><div class="col-xl-5"><div class="card shadow-sm h-100"><div class="card-header"><h5 class="mb-0">Distribusi Status Project</h5></div><div class="card-body"><div id="teamStatusChart"></div></div></div></div></div>
<div class="card shadow-sm team-performance-table"><div class="card-header d-flex justify-content-between align-items-center"><div><h5 class="mb-1">Ranking Seluruh Anggota</h5><small class="text-muted">Anggota dengan overdue diberi penanda merah.</small></div><span class="badge bg-light-primary text-primary"><?= count($members) ?> anggota</span></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-4">Anggota</th><th>Job Title</th><th class="text-center">Aktif</th><th class="text-center">Selesai</th><th class="text-center">Terlambat</th><th class="text-center">Completion</th><th class="text-end pe-4">Aksi</th></tr></thead><tbody><?php if (!$members) : ?><tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data anggota.</td></tr><?php endif; ?><?php foreach ($members as $member) : ?><tr class="<?= (int) ($member['overdue'] ?? 0) > 0 ? 'overdue-row' : '' ?>"><td class="ps-4"><strong><?= esc($member['name']) ?></strong><small class="d-block text-muted"><?= esc($member['role']) ?></small></td><td><?= esc($member['job']) ?></td><td class="text-center fw-bold text-primary"><?= (int) $member['active_tasks'] ?></td><td class="text-center fw-bold text-success"><?= (int) $member['completed'] ?></td><td class="text-center fw-bold text-danger"><?= (int) $member['late'] ?></td><td class="text-center"><span class="badge bg-light-success text-success"><?= number_format((float) ($member['completion_rate'] ?? 0), 1) ?>%</span><?php if ((int) ($member['overdue'] ?? 0) > 0) : ?><small class="d-block text-danger mt-1"><?= (int) $member['overdue'] ?> overdue</small><?php endif; ?></td><td class="text-end pe-4"><a href="<?= base_url('/projects/user/' . (int) $member['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-kanban me-1"></i>Lihat Project</a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<div class="alert alert-light-primary mt-4"><strong>Kesimpulan:</strong> Tim menyelesaikan <?= (int) ($stats['total_completed'] ?? 0) ?> project; <?= (int) ($stats['late_done'] ?? 0) ?> selesai terlambat dan <?= (int) ($stats['overdue'] ?? 0) ?> masih overdue.</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const members = <?= json_encode($members, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const metricLabels = {
        completed: 'Project Selesai',
        active_tasks: 'Project Aktif',
        late: 'Terlambat',
        overdue: 'Overdue',
        completion_rate: 'Completion Rate'
    };
    
    const chartOptions = {
        chart: { type: 'bar', height: 340, toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '58%' } },
        series: [{
            name: metricLabels.completed,
            data: members.slice().sort((a, b) => Number(b.completed) - Number(a.completed)).slice(0, 10).map(m => Number(m.completed))
        }],
        xaxis: {
            categories: members.slice().sort((a, b) => Number(b.completed) - Number(a.completed)).slice(0, 10).map(m => m.name),
            min: 0,
            forceNiceScale: true
        },
        dataLabels: { enabled: true },
        colors: ['#435ebe'],
        tooltip: { y: { formatter: v => metricLabels.completed === 'Completion Rate' ? v + '%' : v + ' project' } }
    };
    
    const chart = new ApexCharts(document.querySelector('#teamMemberChart'), chartOptions);
    chart.render();
    
    document.querySelector('#teamMetricSelect').addEventListener('change', function() {
        const key = this.value;
        const top = members.slice().sort((a, b) => Number(b[key]) - Number(a[key])).slice(0, 10);
        
        chart.updateOptions({
            series: [{ name: metricLabels[key], data: top.map(m => Number(m[key])) }],
            xaxis: { categories: top.map(m => m.name) },
            colors: [key === 'late' || key === 'overdue' ? '#dc3545' : key === 'completed' ? '#198754' : '#435ebe'],
            tooltip: { y: { formatter: v => key === 'completion_rate' ? v + '%' : v + ' project' } }
        });
    });
    
    new ApexCharts(document.querySelector('#teamStatusChart'), {
        chart: { type: 'donut', height: 300 },
        series: <?= json_encode(array_values($statusCounts)) ?>,
        labels: <?= json_encode(array_keys($statusCounts)) ?>,
        legend: { position: 'bottom' }
    }).render();
});
</script>
<?= $this->endSection() ?>
