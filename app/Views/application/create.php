<?php
/** @var array<int, array<string, mixed>> $criticalityOptions */
/** @var array<int, array<string, mixed>> $users */
/** @var string $formAction */
/** @var string $submitLabel */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-heading application-form-wrap mx-auto">
    <div class="mb-3">
        <a href="<?= base_url('/aplikasi') ?>" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Kembali ke Kelola Aplikasi
        </a>
    </div>
    <h3 class="mb-1">Tambah Aplikasi</h3>
    <p class="text-muted">Tambahkan aplikasi atau service baru ke katalog pengelolaan.</p>
</div>

<?php if ($errors = session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger application-form-wrap mx-auto">
        <strong>Validasi gagal.</strong>
        <ul class="mb-0 mt-2"><?php foreach ($errors as $error): ?><li><?= esc(is_scalar($error) ? (string) $error : '') ?></li><?php endforeach ?></ul>
    </div>
<?php endif ?>
    
<?php if ($error = session()->getFlashdata('error')): ?>
    <div class="alert alert-danger application-form-wrap mx-auto"><?= esc(is_scalar($error) ? (string) $error : '') ?></div>
<?php endif ?>
<?= $this->include('application/_form') ?><?= $this->endSection() ?>
