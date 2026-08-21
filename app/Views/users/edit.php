<?php
/**
 * @var string $pageTitle
 * @var string $pageSubtitle
 * @var array $user
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
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalResetPassword">
            <i class="bi bi-key-fill me-1"></i> Reset Password
        </button>
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

<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark"><i class="bi bi-key-fill me-2"></i>Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Password user <strong><?= esc($user['name'] ?? '-') ?></strong> akan direset ke <strong><?= esc($defaultPassword ?? 'user123') ?></strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/users/reset-password/' . (int) ($user['id'] ?? 0)) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-warning">Ya, Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
