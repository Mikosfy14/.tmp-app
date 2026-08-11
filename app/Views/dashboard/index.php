<?php

/**
 * View: Dashboard Index
 * @var string $title
 * @var string $name
 * @var string $role_name
 * @var string $category
 */
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-heading">
    <h3>Dashboard</h3>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body px-4 py-4-5">
                    <h4 class="fw-bold text-primary">Selamat datang kembali, <?= esc($name) ?>! </h4>
                    <p class="text-muted mb-0">Anda terdaftar sebagai <strong><?= esc($role_name) ?></strong> (<?= esc($category) ?>).</p>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>