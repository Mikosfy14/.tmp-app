<?php

/**
 * View: Dashboard Index
 * @var string $title
 * @var string $name
 * @var string $role_name
 * @var string $category
 */
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-link navbar-brand fw-bold" href="#">.tmp Project Manager</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Halo, <strong><?= esc($name) ?></strong> (<?= esc($role_name) ?>)</span>
                <a href="<?= base_url('/logout') ?>" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 text-center">
        <div class="card p-5 shadow-sm border-0">
            <h2 class="fw-bold text-success">Login Berhasil! 🎉</h2>
            <p class="text-muted">Selamat datang di Dashboard Utama, <strong><?= esc($name) ?></strong>.</p>
            <p><span class="badge bg-primary"><?= esc($role_name) ?></span> <span class="badge bg-secondary"><?= esc($category) ?></span></p>
        </div>
    </div>

</body>

</html>