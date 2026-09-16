<?php

/** @var array<string, mixed> $application */
$application = $application ?? [];

$criticalityBadge = match ($application['criticality_recovery'] ?? '') {
    'Criticality 1' => 'bg-danger text-white',
    'Criticality 2' => 'bg-warning text-dark',
    'Criticality 3' => 'bg-info text-dark',
    'Criticality 4' => 'bg-success text-white',
    default => 'bg-secondary text-white',
};

$dateValue = static function ($value, string $format = 'd M Y, H:i'): string {
    if (empty($value)) {
        return '-';
    }
    try {
        return date($format, strtotime($value));
    } catch (\Exception $e) {
        return (string) $value;
    }
};

$textValue = static fn($value): string => !empty(trim((string) ($value ?? ''))) ? esc((string) $value) : '-';
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .application-detail-header {
        background-color: var(--bs-card-bg, #ffffff);
        border: 1px solid var(--bs-border-color, #e9ecef);
        border-radius: 12px;
        padding: 1.5rem;
    }

    [data-bs-theme="dark"] .application-detail-header {
        background-color: #1e1e2d !important;
        border-color: #2b2b40 !important;
    }

    [data-bs-theme="dark"] .application-detail-header h3,
    [data-bs-theme="dark"] .application-detail-header .meta-item-value {
        color: #ffffff !important;
    }

    [data-bs-theme="dark"] .application-detail-header .border-top {
        border-color: #2b2b40 !important;
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

    .notes-container {
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color, #e9ecef);
        border-radius: 10px;
        padding: 1.25rem;
        line-height: 1.7;
        font-size: 0.95rem;
    }

    [data-bs-theme="dark"] .notes-container {
        background-color: #252539 !important;
        border-color: #2b2b40 !important;
        color: #e6eaee !important;
    }

    .env-item {
        background-color: var(--bs-body-bg);
        border-color: var(--bs-border-color, #e9ecef) !important;
    }

    [data-bs-theme="dark"] .env-item {
        background-color: #252539 !important;
        border-color: #2b2b40 !important;
    }

    .env-url-link {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.85rem;
        color: var(--bs-primary);
        text-decoration: none;
        word-break: break-all;
    }

    .env-url-link:hover {
        text-decoration: underline;
    }

    [data-bs-theme="dark"] .env-url-link {
        color: #8fa0f0 !important;
    }

    .avatar-initial {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background-color: rgba(67, 94, 190, 0.12);
        color: #435ebe;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    [data-bs-theme="dark"] .avatar-initial {
        background-color: rgba(143, 160, 240, 0.18);
        color: #8fa0f0;
    }

    .pic-item {
        padding: 0.25rem 0;
    }

    @media (max-width: 767.98px) {
        .application-detail-header {
            padding: 1.25rem 1rem;
        }

        .application-header-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            width: 100%;
        }

        .application-header-actions .btn {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Navigation Back Link -->
<div class="mb-3">
    <a href="<?= base_url('/aplikasi') ?>" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Kembali ke Kelola Aplikasi
    </a>
</div>

<!-- Feedback Flash Alert -->
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Executive Header Card (Zero Redundancy) -->
<div class="card application-detail-header shadow-sm mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                <?php if (!empty($application['app_type'])) : ?>
                    <span class="badge bg-light-primary text-primary border border-primary-subtle fw-semibold">
                        <?= esc($application['app_type']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($application['arch_type'])) : ?>
                    <span class="badge bg-light-secondary text-secondary border border-secondary-subtle fw-semibold">
                        <?= esc($application['arch_type']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($application['criticality_recovery'])) : ?>
                    <span class="badge <?= $criticalityBadge ?> fw-semibold">
                        <?= esc($application['criticality_recovery']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <h3 class="h4 fw-bold mb-0 text-body">
                <?= esc($application['app_component'] ?? '-') ?>
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2 align-self-stretch align-self-md-auto application-header-actions">
            <a href="<?= base_url('/aplikasi/edit/' . $application['id']) ?>" class="btn btn-sm btn-outline-primary px-3 py-2 fw-semibold">
                Edit Aplikasi
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#deleteApplicationModal">
                Hapus
            </button>
        </div>
    </div>

    <!-- Key Metrics Integrated Row (Zero Redundancy) -->
    <div class="row g-3 pt-4 mt-3 border-top">
        <div class="col-6 col-md-3">
            <div class="meta-item-label">Platform</div>
            <div class="meta-item-value"><?= $textValue($application['platform'] ?? null) ?></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="meta-item-label">Tipe Deployment</div>
            <div class="meta-item-value"><?= $textValue($application['deployment_type'] ?? null) ?></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="meta-item-label">Tipe Akses</div>
            <div class="meta-item-value"><?= $textValue($application['access_type'] ?? null) ?></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="meta-item-label">Source Code</div>
            <div>
                <?php if ((int) ($application['has_source_code'] ?? 0) === 1) : ?>
                    <span class="badge bg-light-success text-success border border-success-subtle fw-semibold">Tersedia</span>
                <?php else : ?>
                    <span class="badge bg-light-secondary text-secondary border border-secondary-subtle fw-semibold">Tidak Tersedia</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="row g-4">
        <!-- Main Content Area -->
        <div class="col-12 col-lg-8">
            <!-- Application Description -->
            <div class="card shadow-sm mb-4">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Deskripsi Aplikasi</h5>
                </div>
                <div class="card-body pt-3">
                    <?php if (!empty(trim((string) ($application['description'] ?? '')))) : ?>
                        <div class="notes-container text-body">
                            <?= nl2br(esc($application['description'])) ?>
                        </div>
                    <?php else : ?>
                        <div class="text-muted small py-2">
                            Tidak ada deskripsi tambahan untuk aplikasi ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Environment & URLs -->
            <div class="card shadow-sm mb-4">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Lingkungan & Tautan Aplikasi</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="env-item p-3 rounded border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="meta-item-label mb-0">Production Environment</span>
                                    <span class="badge bg-light-success text-success border border-success-subtle py-1" style="font-size: 0.7rem;">PROD</span>
                                </div>
                                <?php if (!empty($application['url_prod'])) : ?>
                                    <a href="<?= esc((string) $application['url_prod'], 'attr') ?>" target="_blank" rel="noopener noreferrer" class="env-url-link text-break d-inline-flex align-items-center gap-1">
                                        <?= esc((string) $application['url_prod']) ?>
                                        <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                <?php else : ?>
                                    <span class="text-muted small">URL Production belum dikonfigurasi</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="env-item p-3 rounded border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="meta-item-label mb-0">UAT Environment</span>
                                    <span class="badge bg-light-warning text-warning border border-warning-subtle py-1" style="font-size: 0.7rem;">UAT</span>
                                </div>
                                <?php if (!empty($application['url_uat'])) : ?>
                                    <a href="<?= esc((string) $application['url_uat'], 'attr') ?>" target="_blank" rel="noopener noreferrer" class="env-url-link text-break d-inline-flex align-items-center gap-1">
                                        <?= esc((string) $application['url_uat']) ?>
                                        <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                <?php else : ?>
                                    <span class="text-muted small">URL UAT belum dikonfigurasi</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="env-item p-3 rounded border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="meta-item-label mb-0">Development Environment</span>
                                    <span class="badge bg-light-info text-info border border-info-subtle py-1" style="font-size: 0.7rem;">DEV</span>
                                </div>
                                <?php if (!empty($application['url_dev'])) : ?>
                                    <a href="<?= esc((string) $application['url_dev'], 'attr') ?>" target="_blank" rel="noopener noreferrer" class="env-url-link text-break d-inline-flex align-items-center gap-1">
                                        <?= esc((string) $application['url_dev']) ?>
                                        <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                <?php else : ?>
                                    <span class="text-muted small">URL Development belum dikonfigurasi</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Governance & Technical Specifications -->
            <div class="card shadow-sm">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Tata Kelola & Spesifikasi Teknis</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <div class="meta-item-label">Business Owner</div>
                            <div class="meta-item-value"><?= $textValue($application['business_owner'] ?? null) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="meta-item-label">System Owner</div>
                            <div class="meta-item-value"><?= $textValue($application['system_owner'] ?? null) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="meta-item-label">Metode Autentikasi (Login Auth)</div>
                            <div class="meta-item-value"><?= $textValue($application['login_auth'] ?? null) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="meta-item-label">Tipe Pengembangan</div>
                            <div class="meta-item-value"><?= $textValue($application['development_type'] ?? null) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="meta-item-label">Vendor Pengembang</div>
                            <div class="meta-item-value"><?= $textValue($application['vendor'] ?? null) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="meta-item-label">Skema Lisensi</div>
                            <div class="meta-item-value"><?= $textValue($application['license_scheme'] ?? null) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-12 col-lg-4">
            <!-- Assigned PIC -->
            <div class="card shadow-sm mb-4">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Penanggung Jawab Aplikasi</h5>
                </div>
                <div class="card-body pt-3">
                    <?php if (!empty($application['assigned_user_name'])) : ?>
                        <?php
                        $picInitial = strtoupper(substr(trim((string) $application['assigned_user_name']), 0, 1));
                        ?>
                        <div class="pic-item d-flex align-items-center gap-3">
                            <div class="avatar-initial">
                                <?= esc($picInitial ?: 'U') ?>
                            </div>
                            <div class="min-width-0 flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-bold text-body small mb-0"><?= esc($application['assigned_user_name']) ?></span>
                                    <span class="badge bg-light-success text-success border border-success-subtle py-1" style="font-size: 0.65rem;">
                                        PIC Aplikasi
                                    </span>
                                </div>
                                <?php if (!empty($application['assigned_user_job_title'])) : ?>
                                    <small class="text-muted d-block" style="font-size: 0.78rem;">
                                        <?= esc($application['assigned_user_job_title']) ?>
                                    </small>
                                <?php endif; ?>
                                <?php if (!empty($application['assigned_user_email'])) : ?>
                                    <small class="text-muted d-block text-break" style="font-size: 0.75rem;">
                                        <?= esc($application['assigned_user_email']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="text-muted small py-2">Belum ada PIC yang ditugaskan untuk aplikasi ini.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Audit Trail & History -->
            <div class="card shadow-sm">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Riwayat Data</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <div class="meta-item-label">Tanggal Dibuat</div>
                            <div class="meta-item-value"><?= $dateValue($application['created_at'] ?? null) ?></div>
                        </div>
                        <div class="pt-2 border-top">
                            <div class="meta-item-label">Terakhir Diperbarui</div>
                            <div class="meta-item-value"><?= $dateValue($application['updated_at'] ?? null) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation (Structure and IDs Preserved) -->
<div class="modal fade" id="deleteApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-2">Hapus Aplikasi</h6>
                <p class="text-muted mb-0">
                    Aplikasi <strong><?= esc($application['app_component'] ?? '') ?></strong> akan dihapus permanen dari sistem. Lanjutkan hanya jika data ini sudah tidak dibutuhkan.
                </p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/aplikasi/delete/' . $application['id']) ?>" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" data-cooldown="3">Ya, Hapus Aplikasi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>