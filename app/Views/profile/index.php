<?php
$user = $user ?? [];
$isActive = (int) ($user['is_active'] ?? 0) === 1;
$initial = strtoupper(substr((string) ($user['name'] ?? 'U'), 0, 1));
$valueOrDash = static fn ($value): string => $value !== null && $value !== '' ? esc($value) : '-';
?>

<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .profile-page-shell { max-width: 980px; margin: 0 auto; }
    .profile-hero { border: 1px solid #e8eaf1; }
    .profile-avatar-wrap { position: relative; flex: 0 0 auto; }
    .profile-avatar { width: 88px; height: 88px; border-radius: 50%; background: #435ebe; border: 4px solid #eef1ff; color: #fff; font-size: 2rem; box-shadow: 0 4px 14px rgba(31,45,61,.16); }
    .profile-avatar-edit-hint { position: absolute; right: 0; bottom: 0; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; color: #435ebe; background: #fff; border: 2px solid #eef1ff; border-radius: 50%; font-size: .8rem; }
    .profile-detail-label { color: #7c8193; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; }
    .profile-detail-value { font-weight: 700; }
    .profile-info-item { padding: 1rem 0; border-bottom: 1px solid #edf0f5; }
    .profile-info-item:last-child { border-bottom: 0; }
    [data-bs-theme="dark"] .profile-hero, [data-bs-theme="dark"] .profile-info-item { border-color: #2b2b40; }
    [data-bs-theme="dark"] .profile-avatar { border-color: #30304a; }
    [data-bs-theme="dark"] .profile-avatar-edit-hint { background: #252539; border-color: #30304a; }
    @media (max-width: 575.98px) { .profile-page-shell { width: 100%; } }
</style>

<div class="profile-page-shell">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h3 class="mb-1">Profil Saya</h3>
            <p class="text-muted mb-0">Kelola informasi pribadi dan keamanan akun Anda.</p>
        </div>
        <a href="<?= base_url('/profile/edit') ?>" class="btn btn-primary">
            <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit Profil
        </a>
    </div>

    <?php if ($success = session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <?= esc($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <div class="card profile-hero shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="profile-avatar-wrap" title="Foto profil akan tersedia pada pengembangan berikutnya">
                    <div class="profile-avatar d-flex align-items-center justify-content-center fw-bold" aria-hidden="true"><?= esc($initial) ?></div>
                </div>
                <div>
                    <h4 class="mb-1"><?= esc($user['name'] ?? '-') ?></h4>
                    <div class="text-muted">@<?= esc($user['username'] ?? '-') ?></div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="badge bg-light-primary text-primary"><?= esc($user['role_name'] ?? '-') ?></span>
                        <span class="badge bg-light-info text-info"><?= esc($user['category'] ?? '-') ?></span>
                        <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>"><?= $isActive ? 'Aktif' : 'Non-Aktif' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header"><h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-person-vcard me-2 text-primary"></i>Informasi Profil</h5></div>
        <div class="card-body p-4">
            <div class="row g-0">
                <div class="col-md-6 pe-md-4">
                    <div class="profile-info-item"><div class="profile-detail-label">Nama Lengkap</div><div class="profile-detail-value"><?= esc($user['name'] ?? '-') ?></div></div>
                    <div class="profile-info-item"><div class="profile-detail-label">Username</div><div class="profile-detail-value">@<?= esc($user['username'] ?? '-') ?></div></div>
                    <div class="profile-info-item"><div class="profile-detail-label">Email</div><div class="profile-detail-value"><?= $valueOrDash($user['email'] ?? null) ?></div></div>
                </div>
                <div class="col-md-6 ps-md-4">
                    <div class="profile-info-item"><div class="profile-detail-label">Nomor Telepon</div><div class="profile-detail-value"><?= $valueOrDash($user['phone_number'] ?? null) ?></div></div>
                    <div class="profile-info-item"><div class="profile-detail-label">Job Title</div><div class="profile-detail-value"><?= $valueOrDash($user['job_title'] ?? null) ?></div></div>
                    <div class="profile-info-item"><div class="profile-detail-label">Bergabung Sejak</div><div class="profile-detail-value"><?= !empty($user['created_at']) ? esc(date('d M Y', strtotime($user['created_at']))) : '-' ?></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
