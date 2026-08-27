<?php
$user = $user ?? [];
$profileInitial = strtoupper(substr((string) ($user['name'] ?? 'U'), 0, 1));
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .profile-edit-shell { max-width: 980px; margin: 0 auto; }
    .profile-edit-identity { display: flex; align-items: center; gap: 1rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid #edf0f5; }
    .profile-edit-avatar-wrap { position: relative; flex: 0 0 auto; }
    .profile-edit-avatar { width: 68px; height: 68px; border-radius: 50%; color: #fff; background: #435ebe; border: 3px solid #eef1ff; font-size: 1.5rem; }
    .profile-edit-avatar-hint { position: absolute; right: -1px; bottom: -1px; display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; color: #435ebe; background: #fff; border: 2px solid #eef1ff; border-radius: 50%; font-size: .7rem; }
    .profile-edit-section { padding: 1.5rem; border-bottom: 1px solid #edf0f5; }
    .profile-edit-section:last-child { border-bottom: 0; }
    .profile-edit-section h5 { font-size: 1rem; font-weight: 700; }
    .profile-edit-card .form-control { min-height: 44px; border-radius: 6px; }
    .profile-password-group .form-control { border-right: 0; border-radius: 6px 0 0 6px; }
    .profile-password-toggle { min-width: 46px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--bs-border-color); border-left: 0; border-radius: 0 6px 6px 0; background: var(--bs-body-bg); color: var(--bs-secondary-color); }
    .profile-password-toggle:hover, .profile-password-toggle:focus { background: var(--bs-tertiary-bg); color: var(--bs-primary); }
    .profile-password-toggle i { line-height: 1; font-size: 1rem; }
    .profile-readonly { min-height: 44px; padding: .65rem .85rem; background: #f5f6fa; border: 1px solid #e2e5ed; border-radius: 6px; }
    .profile-edit-actions { padding: 1rem 1.5rem; background: var(--bs-body-bg); border-top: 1px solid #e8eaf1; }
    [data-bs-theme="dark"] .profile-edit-identity, [data-bs-theme="dark"] .profile-edit-section, [data-bs-theme="dark"] .profile-edit-actions { border-color: #2b2b40; }
    [data-bs-theme="dark"] .profile-edit-avatar { border-color: #30304a; }
    [data-bs-theme="dark"] .profile-edit-avatar-hint { background: #252539; border-color: #30304a; }
    [data-bs-theme="dark"] .profile-readonly { background: #252539; border-color: #36364f; }
    @media (max-width: 575.98px) { .profile-edit-section { padding: 1rem; } .profile-edit-actions { padding: 1rem; } .profile-edit-actions .btn { width: 100%; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="profile-edit-shell">
    <div class="mb-4">
        <a href="<?= base_url('/profile') ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke Profil</a>
        <h3 class="mb-1">Edit Profil</h3>
        <p class="text-muted mb-0">Perbarui informasi pribadi atau ganti password akun Anda.</p>
    </div>

    <?php $errors = session()->getFlashdata('errors'); $passwordErrors = session()->getFlashdata('password_errors'); $passwordError = session()->getFlashdata('password_error'); ?>
    <?php if (!empty($errors) || !empty($passwordErrors) || $passwordError) : ?>
        <div class="alert alert-danger mb-4">
            <?php foreach ((array) $errors as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?>
            <?php foreach ((array) $passwordErrors as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?>
            <?php if ($passwordError) : ?><div><?= esc($passwordError) ?></div><?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card profile-edit-card shadow-sm mb-4">
        <form action="<?= base_url('/profile/update') ?>" method="post">
            <?= csrf_field() ?>
            <div class="profile-edit-identity">
                <div class="profile-edit-avatar-wrap" title="Upload foto profil akan tersedia pada pengembangan berikutnya">
                    <div class="profile-edit-avatar d-flex align-items-center justify-content-center fw-bold" aria-hidden="true"><?= esc($profileInitial) ?></div>
                    <span class="profile-edit-avatar-hint" aria-hidden="true"><i class="bi bi-camera-fill"></i></span>
                </div>
                <div>
                    <div class="fw-bold"><?= esc($user['name'] ?? '-') ?></div>
                    <div class="text-muted small">@<?= esc($user['username'] ?? '-') ?></div>
                    <div class="text-muted small">Foto profil belum tersedia.</div>
                </div>
            </div>
            <section class="profile-edit-section">
                <div class="row g-3">
                    <div class="col-md-6"><label for="profileName" class="form-label fw-semibold">Nama Lengkap</label><input id="profileName" type="text" name="name" class="form-control" value="<?= esc(old('name', $user['name'] ?? '')) ?>" required></div>
                    <div class="col-md-6"><label for="profileEmail" class="form-label fw-semibold">Email</label><input id="profileEmail" type="email" name="email" class="form-control" value="<?= esc(old('email', $user['email'] ?? '')) ?>"></div>
                    <div class="col-md-6"><label for="profilePhone" class="form-label fw-semibold">Nomor Telepon</label><input id="profilePhone" type="tel" name="phone_number" class="form-control" value="<?= esc(old('phone_number', $user['phone_number'] ?? '')) ?>"></div>
                    <div class="col-md-6"><label for="profileJobTitle" class="form-label fw-semibold">Job Title</label><input id="profileJobTitle" type="text" name="job_title" class="form-control" value="<?= esc(old('job_title', $user['job_title'] ?? '')) ?>"></div>
                </div>
            </section>
            <section class="profile-edit-section">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-semibold">Username</label><div class="profile-readonly">@<?= esc($user['username'] ?? '-') ?></div></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Role</label><div class="profile-readonly"><?= esc($user['role_name'] ?? '-') ?></div></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Kategori</label><div class="profile-readonly"><?= esc($user['category'] ?? '-') ?></div></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Status Akun</label><div class="profile-readonly"><?= (int) ($user['is_active'] ?? 0) === 1 ? 'Aktif' : 'Non-Aktif' ?></div></div>
                </div>
            </section>
            <div class="profile-edit-actions d-flex justify-content-end gap-2">
                <a href="<?= base_url('/profile') ?>" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle" aria-hidden="true"></i> Simpan Perubahan Profil</button>
            </div>
        </form>
    </div>

    <div class="card profile-edit-card shadow-sm">
        <form action="<?= base_url('/profile/change-password') ?>" method="post">
            <?= csrf_field() ?>
            <section class="profile-edit-section">
                <h5 class="mb-1">Ganti Password</h5>
                <p class="text-muted small mb-4">Gunakan minimal 8 karakter dan jangan gunakan password yang mudah ditebak.</p>
                <div class="row g-3">
                    <div class="col-12"><label for="currentPassword" class="form-label fw-semibold">Password Lama</label><div class="input-group profile-password-group"><input id="currentPassword" type="password" name="current_password" class="form-control" autocomplete="current-password" required><button type="button" class="profile-password-toggle" data-password-toggle="currentPassword" data-password-label="password lama" aria-label="Tampilkan password lama" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                    <div class="col-md-6"><label for="newPassword" class="form-label fw-semibold">Password Baru</label><div class="input-group profile-password-group"><input id="newPassword" type="password" name="new_password" class="form-control" autocomplete="new-password" minlength="8" required><button type="button" class="profile-password-toggle" data-password-toggle="newPassword" data-password-label="password baru" aria-label="Tampilkan password baru" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                    <div class="col-md-6"><label for="newPasswordConfirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label><div class="input-group profile-password-group"><input id="newPasswordConfirmation" type="password" name="new_password_confirmation" class="form-control" autocomplete="new-password" minlength="8" required><button type="button" class="profile-password-toggle" data-password-toggle="newPasswordConfirmation" data-password-label="konfirmasi password baru" aria-label="Tampilkan konfirmasi password baru" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                </div>
            </section>
            <div class="profile-edit-actions d-flex justify-content-end"><button type="submit" class="btn btn-warning"><i class="bi bi-key-fill" aria-hidden="true"></i> Ganti Password</button></div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.getElementById(button.dataset.passwordToggle);
                const icon = button.querySelector('i');

                if (!input || !icon) {
                    return;
                }

                const willShow = input.type === 'password';
                input.type = willShow ? 'text' : 'password';
                icon.classList.toggle('bi-eye', !willShow);
                icon.classList.toggle('bi-eye-slash', willShow);
                button.setAttribute('aria-pressed', String(willShow));
                button.setAttribute('aria-label', (willShow ? 'Sembunyikan ' : 'Tampilkan ') + (button.dataset.passwordLabel || 'password'));
            });
        });
    });
</script>
<?= $this->endSection() ?>
