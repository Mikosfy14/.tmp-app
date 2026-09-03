<?php

/** @var array<int, array<string, mixed>> $applications */
/** @var array<int, array<string, mixed>> $criticalityOptions */
/** @var string $keyword */
/** @var int|null $selectedCriticality */

$applications = $applications ?? [];
$criticalityOptions = $criticalityOptions ?? [];
$keyword = $keyword ?? '';
$selectedCriticality = $selectedCriticality ?? null;
$criticalityClass = static fn(?string $name) => match ($name) {
    'Criticality 1' => 'danger',
    'Criticality 2' => 'warning',
    'Criticality 3' => 'info',
    'Criticality 4' => 'success',
    default => 'secondary'
};
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .application-filter-action {
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        line-height: 1;
    }

    .application-filter-reset {
        width: 100%;
        min-width: 0;
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .application-filter-reset i {
        line-height: 1;
        font-size: 1.05rem;
    }

    @media (max-width: 991.98px) {
        .application-filter-action {
            width: 100%;
        }

        .application-filter-reset {
            width: 100%;
            min-width: 0;
            min-height: 38px;
            padding-inline: .75rem;
            flex: 1 1 auto;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-heading d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="mb-1">Aplikasi Pengelolaan</h3>
        <p class="text-muted mb-0">Katalog aplikasi dan service yang dikelola tim.</p>
    </div>
    <?php
    $exportParams = [];
    if (!empty($selectedCriticality)) $exportParams['criticality_recovery_id'] = $selectedCriticality;
    if (!empty($keyword)) $exportParams['keyword'] = $keyword;
    $exportQueryString = !empty($exportParams) ? '?' . http_build_query($exportParams) : '';
    ?>
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary text-body d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-export text-body"></i>
                <span>Ekspor Data</span>
                <i class="fas fa-chevron-down text-body" style="font-size: 0.7rem; line-height: 1;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= base_url('/aplikasi/export/excel' . $exportQueryString) ?>">
                        <i class="fas fa-file-excel text-success fs-5"></i>
                        <div>
                            <strong class="d-block text-dark">Download Excel (.xlsx)</strong>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= base_url('/aplikasi/export/pdf' . $exportQueryString) ?>" target="_blank">
                        <i class="fas fa-file-pdf text-danger fs-5"></i>
                        <div>
                            <strong class="d-block text-dark">Download PDF (.pdf)</strong>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
        <a href="<?= base_url('/aplikasi/create') ?>" class="btn btn-primary d-flex align-items-center gap-1">
            <i class="fas fa-plus-square me-1"></i> Tambah Aplikasi
        </a>
    </div>
</div>
<?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $class): ?><?php if ($message = session()->getFlashdata($key)): ?><div class="alert alert-<?= $class ?> alert-dismissible fade show"><?= esc(is_scalar($message) ? (string) $message : '') ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif ?><?php endforeach ?>
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= base_url('/aplikasi') ?>" class="row g-2 align-items-center">
            <div class="col-lg-6"><label class="visually-hidden" for="keyword">Pencarian</label>
                <div class="input-group">
                    <input id="keyword" name="keyword" class="form-control" value="<?= esc($keyword) ?>" placeholder="Cari nama, deskripsi, owner, PIC, atau URL">
                    <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                </div>
            </div>
            <div class="col-md-7 col-lg-4"><select name="criticality_recovery_id" class="form-select">
                    <option value="">Semua Criticality</option><?php foreach ($criticalityOptions as $item): ?><option value="<?= (int) ($item['id'] ?? 0) ?>" <?= (int)$selectedCriticality === (int)($item['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($item['criticality_name'] ?? '')) ?></option><?php endforeach ?>
                </select></div>
            <div class="col-12 col-md-6 col-lg-1 d-flex"><button class="btn btn-primary application-filter-action w-100 px-2" title="Terapkan filter" aria-label="Terapkan filter"><i class="bi bi-search" aria-hidden="true"></i><span class="d-inline d-lg-none ms-1">Cari</span></button></div>
            <div class="col-12 col-md-6 col-lg-1 d-flex justify-content-lg-end"><a href="<?= base_url('/aplikasi') ?>" class="btn btn-outline-secondary application-filter-action application-filter-reset" title="Reset filter" aria-label="Reset filter"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i><span class="d-inline d-lg-none ms-1">Reset</span></a></div>
        </form>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Aplikasi</th>
                        <th>Criticality</th>
                        <th>Platform</th>
                        <th>PIC</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$applications): ?><tr>
                            <td colspan="5" class="text-center py-5">
                                <p class="text-muted mt-2 mb-0">Belum ada aplikasi yang sesuai.</p>
                            </td>
                        </tr><?php endif ?>
                    <?php foreach ($applications as $app): $criticalityName = $app['criticality_recovery'] ?? null;
                        $color = $criticalityClass(is_scalar($criticalityName) ? (string) $criticalityName : null); ?><tr>
                            <td class="ps-4 py-3"><strong><?= esc((string) ($app['app_component'] ?? '')) ?></strong><small class="d-block text-muted text-truncate" style="max-width:380px"><?= esc((string) ($app['description'] ?: '-')) ?></small><span class="badge bg-light-secondary text-secondary mt-1"><?= esc((string) ($app['app_type'] ?: 'Tidak dikategorikan')) ?></span></td>
                            <td><span class="badge bg-light-<?= $color ?> text-<?= $color ?>"><?= esc((string) ($app['criticality_recovery'] ?: '-')) ?></span><small class="d-block text-muted mt-1"><?= esc((string) ($app['criticality_recovery_description'] ?: '')) ?></small></td>
                            <td><span class="fw-semibold"><?= esc((string) ($app['platform'] ?: '-')) ?></span><small class="d-block text-muted"><?= esc((string) ($app['deployment_type'] ?: '-')) ?></small></td>
                            <td><?= esc((string) ($app['assigned_user_name'] ?: '---')) ?></td>
                            <td class="text-center pe-4 text-nowrap">
                                <div class="d-inline-flex gap-1"><a href="<?= base_url('/aplikasi/detail/' . $app['id']) ?>" class="btn btn-sm btn-outline-primary" title="Detail Aplikasi"><i class="bi bi-eye-fill me-1"></i>Detail</a><a href="<?= base_url('/aplikasi/edit/' . $app['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit Aplikasi"><i class="bi bi-pencil-square me-1"></i>Edit</a></div>
                            </td>
                        </tr><?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>