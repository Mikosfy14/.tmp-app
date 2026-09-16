<?php
/** @var array<string, mixed> $application */
/** @var array<int, array<string, mixed>> $criticalityOptions */
/** @var array<int, array<string, mixed>> $users */
/** @var string $formAction */
/** @var string $submitLabel */
$application = $application ?? [];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-heading">
    <div class="mb-3">
        <a href="<?= base_url('/aplikasi/detail/' . (int) ($application['id'] ?? 0)) ?>" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Kembali ke Detail Aplikasi
        </a>
    </div>
    <h3 class="mb-1">Edit Aplikasi</h3>
    <p class="text-muted">Perbarui informasi <?= esc((string) ($application['app_component'] ?? 'aplikasi')) ?>.</p>
</div>

<?php if ($errors = session()->getFlashdata('errors')): ?><div class="alert alert-danger"><strong>Validasi gagal.</strong>
        <ul class="mb-0 mt-2"><?php foreach ($errors as $error): ?><li><?= esc(is_scalar($error) ? (string) $error : '') ?></li><?php endforeach ?></ul>
    </div><?php endif ?>

<?php if ($error = session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(is_scalar($error) ? (string) $error : '') ?></div><?php endif ?>
<?= $this->include('application/_form') ?><?= $this->endSection() ?>
