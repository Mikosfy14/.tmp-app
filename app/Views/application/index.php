<?php

/** @var array<int, array<string, mixed>> $applications */
/** @var array<int, array<string, mixed>> $criticalityOptions */
/** @var string $keyword */
/** @var int|null $selectedCriticality */
/** @var bool $managedByMe */
/** @var \CodeIgniter\Pager\Pager|null $pager */

$applications = $applications ?? [];
$criticalityOptions = $criticalityOptions ?? [];
$keyword = $keyword ?? '';
$selectedCriticality = $selectedCriticality ?? null;
$managedByMe = !empty($managedByMe);
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

    @media (max-width: 767.98px) {
        .application-page-heading {
            flex-direction: column;
            align-items: stretch !important;
            gap: .85rem;
        }

        .application-header-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: .5rem;
        }

        .application-header-actions .dropdown {
            width: 100%;
            height: 100%;
            display: flex;
        }

        .application-header-actions .dropdown > button,
        .application-header-actions > a {
            width: 100%;
            height: 100%;
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
        }
    }

    .filter-chevron {
        transition: transform 0.2s ease;
    }

    [data-bs-toggle="collapse"][aria-expanded="true"] .filter-chevron {
        transform: rotate(180deg);
    }

    .application-mobile-card {
        border-radius: 0.75rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .application-mobile-specs-box {
        background-color: #f8f9fa;
        border: 1px solid #edf2f7;
    }

    [data-bs-theme="dark"] .application-mobile-card {
        background-color: var(--bs-card-bg, #1e1e2d) !important;
        border-color: var(--bs-border-color, #2b2b40) !important;
    }

    [data-bs-theme="dark"] .application-mobile-card .application-mobile-title {
        color: #f5f7ff !important;
    }

    [data-bs-theme="dark"] .application-mobile-specs-box {
        background-color: rgba(255, 255, 255, 0.04) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
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

    .application-pagination .pagination {
        margin: 0;
        gap: .35rem;
    }

    .application-pagination .page-item .page-link {
        border: 0;
        border-radius: .55rem;
        min-width: 2.25rem;
        text-align: center;
        color: #52606d;
        font-weight: 600;
    }

    .application-pagination .page-item.active .page-link {
        background: #435ebe;
        color: #fff;
        box-shadow: 0 .25rem .65rem rgba(67, 94, 190, .25);
    }

    .application-pagination .page-item:not(.active) .page-link:hover {
        background: #eef1ff;
        color: #435ebe;
    }

    .application-pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background: #f1f3f5;
        opacity: .75;
        cursor: not-allowed;
        pointer-events: none;
    }

    [data-bs-theme="dark"] .application-pagination .page-item:not(.active) .page-link {
        color: #a0aec0;
        background: transparent;
    }

    [data-bs-theme="dark"] .application-pagination .page-item:not(.active) .page-link:hover {
        background: rgba(67, 94, 190, 0.2);
        color: #8fa0f0;
    }

    [data-bs-theme="dark"] .application-pagination .page-item.disabled .page-link {
        color: #607080;
        background: rgba(255, 255, 255, 0.05);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-heading application-page-heading d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="mb-1">Application</h3>
        <p class="text-muted mb-0">Katalog aplikasi dan service yang dikelola tim.</p>
    </div>
    <?php
    $exportParams = [];
    if (!empty($selectedCriticality)) $exportParams['criticality_recovery_id'] = $selectedCriticality;
    if (!empty($keyword)) $exportParams['keyword'] = $keyword;
    if ($managedByMe) $exportParams['managed_by_me'] = 1;
    $exportQueryString = !empty($exportParams) ? '?' . http_build_query($exportParams) : '';
    ?>
    <div class="d-flex align-items-center gap-2 application-header-actions">
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
<?php
$hasActiveAdvancedFilter = !empty($selectedCriticality) || $managedByMe;
?>
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= base_url('/aplikasi') ?>">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-5">
                    <label class="visually-hidden" for="keyword">Pencarian</label>
                    <div class="input-group">
                        <input id="keyword" name="keyword" class="form-control" value="<?= esc($keyword) ?>" placeholder="Cari nama, deskripsi, owner, PIC, atau URL">
                        <?php if (!empty($keyword)) : ?>
                            <a href="<?= base_url('/aplikasi') ?>" class="btn btn-outline-secondary d-lg-none d-flex align-items-center px-2" title="Hapus pencarian">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary d-lg-none d-flex align-items-center px-3" title="Cari" aria-label="Cari">
                            <i class="bi bi-search"></i>
                        </button>
                        <span class="input-group-text bg-transparent d-none d-lg-flex"><i class="bi bi-search"></i></span>
                    </div>
                </div>

                <!-- Mobile filter toggle button (< lg) -->
                <div class="col-12 d-lg-none">
                    <button class="btn btn-sm btn-outline-secondary w-100 d-flex align-items-center justify-content-between py-2" type="button" data-bs-toggle="collapse" data-bs-target="#applicationAdvancedFilterCollapse" aria-expanded="<?= $hasActiveAdvancedFilter ? 'true' : 'false' ?>" aria-controls="applicationAdvancedFilterCollapse">
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="bi bi-funnel"></i>
                            <span>Filter Lanjutan</span>
                            <?php if ($hasActiveAdvancedFilter) : ?>
                                <span class="badge bg-primary rounded-pill">Aktif</span>
                            <?php endif; ?>
                        </span>
                        <i class="bi bi-chevron-down filter-chevron"></i>
                    </button>
                </div>

                <!-- Collapsible filter content on mobile, always visible on desktop (>= lg) -->
                <div class="col-12 col-lg-7 collapse d-lg-block <?= $hasActiveAdvancedFilter ? 'show' : '' ?>" id="applicationAdvancedFilterCollapse">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5 col-lg-5">
                            <label class="visually-hidden" for="criticalitySelect">Tingkat Criticality</label>
                            <select id="criticalitySelect" name="criticality_recovery_id" class="form-select">
                                <option value="">All Criticality</option>
                                <?php foreach ($criticalityOptions as $item): ?>
                                    <option value="<?= (int) ($item['id'] ?? 0) ?>" <?= (int)$selectedCriticality === (int)($item['id'] ?? 0) ? 'selected' : '' ?>>
                                        <?= esc((string) ($item['criticality_name'] ?? '')) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 col-lg-3">
                            <div class="form-check form-switch mb-0 d-flex align-items-center gap-2" style="min-height: 38px;">
                                <input class="form-check-input mt-0" type="checkbox" role="switch" name="managed_by_me" value="1" id="managedByMe" <?= $managedByMe ? 'checked' : '' ?> onchange="this.form.submit()">
                                <label class="form-check-label text-nowrap fw-semibold small" for="managedByMe">Dikelola oleh saya</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 col-lg-2 d-flex">
                            <button type="submit" class="btn btn-primary application-filter-action w-100 px-2" title="Terapkan filter" aria-label="Terapkan filter">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <span class="d-inline d-lg-none ms-1">Cari</span>
                            </button>
                        </div>
                        <div class="col-6 col-md-2 col-lg-2 d-flex justify-content-lg-end">
                            <a href="<?= base_url('/aplikasi') ?>" class="btn btn-outline-secondary application-filter-action application-filter-reset" title="Reset filter" aria-label="Reset filter">
                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                <span class="d-inline d-lg-none ms-1">Reset</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <!-- Desktop Table View -->
        <div class="table-responsive d-none d-md-block">
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

        <!-- Mobile Cards View -->
        <div class="d-block d-md-none p-3">
            <?php if (!empty($applications)) : ?>
                <div class="application-mobile-feed">
                    <?php foreach ($applications as $app): 
                        $criticalityName = $app['criticality_recovery'] ?? null;
                        $color = $criticalityClass(is_scalar($criticalityName) ? (string) $criticalityName : null);
                    ?>
                        <div class="card border shadow-sm mb-3 application-mobile-card">
                            <div class="card-body p-3">
                                <!-- Top Meta Row: App Type & Criticality Badges -->
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                    <span class="badge bg-light-secondary text-secondary" style="font-size: 0.72rem;">
                                        <?= esc((string) ($app['app_type'] ?: 'Tidak dikategorikan')) ?>
                                    </span>
                                    <span class="badge bg-light-<?= $color ?> text-<?= $color ?>">
                                        <?= esc((string) ($app['criticality_recovery'] ?: '-')) ?>
                                    </span>
                                </div>

                                <!-- Application Title & Description -->
                                <h6 class="fw-bold mb-1">
                                    <a href="<?= base_url('/aplikasi/detail/' . $app['id']) ?>" class="text-dark text-decoration-none application-mobile-title">
                                        <?= esc((string) ($app['app_component'] ?? '')) ?>
                                    </a>
                                </h6>
                                <?php if (!empty($app['description'])): ?>
                                    <p class="text-muted small mb-2 text-truncate" style="max-width: 100%;">
                                        <?= esc((string) $app['description']) ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Specs Box (2-column: Platform & Deployment) -->
                                <div class="application-mobile-specs-box rounded-2 p-2 mb-2">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="text-muted" style="font-size: 0.72rem; line-height: 1.2;">
                                                Platform
                                            </div>
                                            <div class="fw-semibold text-body small mt-1">
                                                <?= esc((string) ($app['platform'] ?: '-')) ?>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted" style="font-size: 0.72rem; line-height: 1.2;">
                                                Deployment
                                            </div>
                                            <div class="fw-semibold text-body small mt-1">
                                                <?= esc((string) ($app['deployment_type'] ?: '-')) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($app['criticality_recovery_description'])): ?>
                                        <div class="pt-2 mt-2 border-top border-secondary-subtle">
                                            <div class="text-muted" style="font-size: 0.7rem; line-height: 1.2;">
                                                Recovery: <span class="text-body"><?= esc((string) $app['criticality_recovery_description']) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- PIC Section -->
                                <div class="mb-3">
                                    <div class="text-muted small mb-1">PIC Pengelola:</div>
                                    <?php if (!empty($app['assigned_user_name'])): ?>
                                        <span class="badge bg-light-primary text-primary" style="font-size: 0.75rem;">
                                            <?= esc((string) $app['assigned_user_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Belum ada PIC</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons (50% / 50%) -->
                                <div class="row g-2 pt-2 border-top">
                                    <div class="col-6">
                                        <a href="<?= base_url('/aplikasi/detail/' . $app['id']) ?>" class="btn btn-sm btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center py-2">
                                            Detail
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="<?= base_url('/aplikasi/edit/' . $app['id']) ?>" class="btn btn-sm btn-outline-warning w-100 d-inline-flex align-items-center justify-content-center py-2">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted mt-2 mb-0">Belum ada aplikasi yang sesuai.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($applications) && !empty($pager) && $pager->getPageCount('applications') > 1) : ?>
            <div class="application-pagination d-flex justify-content-center justify-content-md-end p-3 border-top">
                <?= $pager->links('applications', 'complete') ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>