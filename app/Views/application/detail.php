<?php
/** @var array<string, mixed> $application */
$application = $application ?? [];
$a = $application;
$show = static function ($value): string {
    return $value !== null && $value !== '' && is_scalar($value) ? esc((string) $value) : '-';
};
$fields = ['App Component' => 'app_component', 'Description' => 'description', 'App Type' => 'app_type', 'Architecture Type' => 'arch_type', 'Criticality Recovery' => 'criticality_recovery', 'Access Type' => 'access_type', 'Login Auth' => 'login_auth', 'Platform' => 'platform', 'URL Production' => 'url_prod', 'URL Development' => 'url_dev', 'URL UAT' => 'url_uat', 'Development Type' => 'development_type', 'Vendor' => 'vendor', 'License Scheme' => 'license_scheme', 'Deployment Type' => 'deployment_type', 'Business Owner' => 'business_owner', 'System Owner' => 'system_owner', 'Assigned PIC' => 'assigned_user_name']; ?>
<style>
    .application-detail-page .page-heading .btn i {
        vertical-align: -0.08em;
    }

    .application-detail-page .detail-label-icon {
        width: 1.25rem;
        display: inline-flex;
        justify-content: center;
        margin-right: .35rem;
    }

    .application-detail-page .btn {
        display: inline-flex;
        align-items: center;
    }
</style>
<?= $this->extend('layouts/main') ?><?= $this->section('content') ?>
<div class="application-detail-page">
    <div class="page-heading d-flex flex-wrap justify-content-between gap-3">
        <div><a href="<?= base_url('/aplikasi') ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali ke Aplikasi</a>
            <h3 class="mb-1"><?= $show($a['app_component'] ?? null) ?></h3>
            <p class="text-muted">Detail lengkap aplikasi dan kepemilikannya.</p>
        </div>
        <div class="align-self-end mb-3 d-flex gap-2"><a href="<?= base_url('/aplikasi/edit/' . $a['id']) ?>" class="btn btn-warning"><i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Edit Aplikasi</a> <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteApplicationModal"><i class="bi bi-trash-fill me-1" aria-hidden="true"></i>Hapus Aplikasi</button></div>
    </div>
    <?php if ($message = session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(is_scalar($message) ? (string) $message : '') ?></div><?php endif ?>
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="mb-4">Informasi Aplikasi</h5>
                    <div class="row g-4"><?php foreach ($fields as $label => $key): ?><div class="<?= in_array($key, ['description', 'url_prod', 'url_dev', 'url_uat'], true) ? 'col-12' : 'col-md-6' ?>"><small class="text-muted d-block mb-1"><?= $label ?></small><?php if (str_starts_with($key, 'url_') && !empty($a[$key]) && is_scalar($a[$key])): ?><a href="<?= esc((string) $a[$key], 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc((string) $a[$key]) ?> <i class="bi bi-box-arrow-up-right"></i></a><?php else: ?><span class="fw-semibold"><?= $show($a[$key] ?? null) ?></span><?php endif ?></div><?php endforeach ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5>Status Pengelolaan</h5>
                    <div class="d-flex justify-content-between py-3 border-bottom"><span>Source Code</span><span class="badge bg-light-<?= (int)$a['has_source_code'] === 1 ? 'success' : 'secondary' ?> text-<?= (int)$a['has_source_code'] === 1 ? 'success' : 'secondary' ?>"><?= (int)$a['has_source_code'] === 1 ? 'Tersedia' : 'Tidak tersedia' ?></span></div>
                    <div class="pt-3"><small class="text-muted d-block">PIC</small><strong><?= $show($a['assigned_user_name'] ?? null) ?></strong><?php if (!empty($a['assigned_user_email']) && is_scalar($a['assigned_user_email'])): ?><small class="d-block text-muted"><?= esc((string) $a['assigned_user_email']) ?></small><?php endif ?></div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5>Riwayat Data</h5><small class="text-muted d-block mt-3">Dibuat</small><span><?= $show($a['created_at'] ?? null) ?></span><small class="text-muted d-block mt-3">Terakhir diperbarui</small><span><?= $show($a['updated_at'] ?? null) ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteApplicationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Aplikasi</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus <strong><?= $show($a['app_component'] ?? null) ?></strong>?</p>
                    <div class="alert alert-light-danger mb-0">Data aplikasi akan dihapus permanen dan tidak dapat dipulihkan.</div>
                </div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="post" action="<?= base_url('/aplikasi/delete/' . $a['id']) ?>"><?= csrf_field() ?><button class="btn btn-danger">Ya, Hapus Aplikasi</button></form>
                </div>
            </div>
        </div>
    </div>
</div><?= $this->endSection() ?>
