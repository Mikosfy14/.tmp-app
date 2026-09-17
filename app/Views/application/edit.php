<?php
/** @var array<string, mixed> $application */
/** @var array<int, array<string, mixed>> $criticalityOptions */
/** @var array<int, array<string, mixed>> $users */
/** @var string $formAction */
/** @var string $submitLabel */
$application = $application ?? [];
helper('navigation');
$defaultBack = !empty($application['id']) ? '/aplikasi/detail/' . (int) $application['id'] : '/aplikasi';
$defaultBackLabel = !empty($application['id']) ? 'Kembali ke Detail Aplikasi' : 'Kembali ke Kelola Aplikasi';
$backNav = get_contextual_back($defaultBack, $defaultBackLabel);
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-heading application-form-wrap mx-auto">
    <div class="mb-3">
        <a href="<?= esc($backNav['url'], 'attr') ?>" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> <?= esc($backNav['label']) ?>
        </a>
    </div>
    <h3 class="mb-1">Edit Aplikasi</h3>
    <p class="text-muted">Perbarui informasi <?= esc((string) ($application['app_component'] ?? 'aplikasi')) ?>.</p>
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
