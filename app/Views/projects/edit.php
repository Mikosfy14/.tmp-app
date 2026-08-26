<?php
/**
 * @var string $pageTitle
 * @var string $pageSubtitle
 * @var array $project
 */
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-heading project-form-page-header">
    <div>
        <a href="<?= base_url('/projects/detail/' . $project['id']) ?>" class="btn btn-sm btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke Detail Project
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

<?php if ($success = session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <?= esc($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error = session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <?= esc($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?= $this->include('projects/_form') ?>

<?= $this->endSection() ?>
