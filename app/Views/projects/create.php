<?php
/**
 * @var string $pageTitle
 * @var string $pageSubtitle
 */
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-heading project-form-page-header">
    <div>
        <a href="<?= base_url('/projects') ?>" class="btn btn-sm btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke Project Tracker
        </a>
        <h3 class="mb-1"><?= esc($pageTitle) ?></h3>
        <p class="text-subtitle text-muted mb-0"><?= esc($pageSubtitle) ?></p>
    </div>
</div>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <strong>Validasi gagal.</strong>
        <ul class="mb-0 mt-2">
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?= $this->include('projects/_form', ['cardTitle' => 'Form Tambah Project']) ?>

<?= $this->endSection() ?>
