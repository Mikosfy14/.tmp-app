<?php
/** @var array<int, array<string, mixed>> $criticalityOptions */
/** @var array<int, array<string, mixed>> $users */
/** @var string $formAction */
/** @var string $submitLabel */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-heading"><a href="<?= base_url('/aplikasi') ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali ke Aplikasi</a>
    <h3 class="mb-1">Tambah Aplikasi</h3>
    <p class="text-muted">Tambahkan aplikasi atau service baru ke katalog pengelolaan.</p>
</div>

<?php if ($errors = session()->getFlashdata('errors')): ?><div class="alert alert-danger"><strong>Validasi gagal.</strong>
        <ul class="mb-0 mt-2"><?php foreach ($errors as $error): ?><li><?= esc(is_scalar($error) ? (string) $error : '') ?></li><?php endforeach ?></ul>
    </div><?php endif ?>
    
<?php if ($error = session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(is_scalar($error) ? (string) $error : '') ?></div><?php endif ?>
<?= $this->include('application/_form') ?><?= $this->endSection() ?>
