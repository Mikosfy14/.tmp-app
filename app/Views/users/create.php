<?php
/**
 * @var string $pageTitle
 * @var string $pageSubtitle
 */
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-heading d-flex justify-content-between align-items-start mb-3">
    <div>
        <a href="<?= base_url('/users') ?>" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Back to User Management
        </a>
        <h3><?= esc($pageTitle) ?></h3>
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

<div class="page-content">
    <div class="row">
        <div class="col-12 col-xl-10 mx-auto">
            <?= $this->include('users/_form') ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
