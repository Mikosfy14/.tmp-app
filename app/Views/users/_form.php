<?php
/**
 * @var array|null $user
 * @var array $roles
 * @var string $formAction
 * @var string $submitLabel
 * @var string $cancelUrl
 * @var string $defaultPassword
 */

$user = $user ?? null;
$roles = $roles ?? [];
$formAction = $formAction ?? '#';
$submitLabel = $submitLabel ?? 'Simpan';
$cancelUrl = $cancelUrl ?? base_url('/users');
$defaultPassword = $defaultPassword ?? 'user1234';

$oldValue = static function (string $field, $fallback = '') {
    $value = old($field);
    return $value !== null ? $value : $fallback;
};

$selectedRoleId = (string) $oldValue('role_id', (string) ($user['role_id'] ?? ''));
$selectedStatus = (string) $oldValue('is_active', (string) ($user['is_active'] ?? '1'));
$roleCategoryMap = [];
foreach ($roles as $role) {
    $roleCategoryMap[(string) $role['id']] = (string) $role['category'];
}
?>

<style>
    .user-form-card {
        max-width: 980px;
        margin-inline: auto;
        overflow: visible;
        border: 1px solid #e8eaf1;
        box-shadow: 0 8px 28px rgba(31, 45, 61, .07) !important;
    }

    .user-form-page-header {
        max-width: 980px;
        margin: 0 auto 1.25rem;
    }

    .user-form-page-header .btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }

    .user-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: #f8f9fc;
    }

    .user-form-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: #435ebe;
        background: #eef1ff;
    }

    .user-form-section-icon {
        width: 34px;
        height: 34px;
        border-radius: 7px;
        font-size: .95rem;
    }

    .user-form-section-icon i,
    .user-form-card .btn i,
    .user-status-indicator i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .user-form-section-icon i {
        font-size: 1rem;
    }

    .user-form-section {
        padding: 1.5rem;
        border-bottom: 1px solid #edf0f5;
    }

    .user-form-section-heading {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        margin-bottom: 1.25rem;
    }

    .user-form-section-heading h5 {
        margin: 0 0 .15rem;
        font-size: 1rem;
        font-weight: 700;
    }

    .user-form-section-heading p {
        margin: 0;
        color: #7c8193;
        font-size: .875rem;
    }

    .user-form-card .form-label {
        margin-bottom: .45rem;
        color: #363b4e;
        font-size: .875rem;
        font-weight: 700;
    }

    .user-form-card .form-control,
    .user-form-card .form-select {
        min-height: 44px;
        border-color: #dfe3eb;
        border-radius: 6px;
    }

    .user-form-card .form-control:focus,
    .user-form-card .form-select:focus {
        border-color: #7185d5;
        box-shadow: 0 0 0 .2rem rgba(67, 94, 190, .12);
    }

    .user-role-preview {
        display: flex;
        align-items: center;
        min-height: 44px;
        padding: .625rem .85rem;
        color: #435ebe;
        background: #f3f5ff;
        border: 1px solid #dfe4fb;
        border-radius: 6px;
        font-weight: 700;
    }

    .user-status-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .user-status-option {
        position: relative;
    }

    .user-status-option .form-check-input {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 1;
        margin: 0;
    }

    .user-status-option .form-check-label {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        width: 100%;
        min-height: 82px;
        padding: .9rem 2.5rem .9rem .9rem;
        cursor: pointer;
        border: 1px solid #dfe3eb;
        border-radius: 7px;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }

    .user-status-option .form-check-input:checked + .form-check-label {
        background: #f3f5ff;
        border-color: #7185d5;
        box-shadow: 0 0 0 .15rem rgba(67, 94, 190, .08);
    }

    .user-status-indicator {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 30px;
        height: 30px;
        border-radius: 50%;
    }

    .user-status-indicator.active {
        color: #24744b;
        background: #dff7ea;
    }

    .user-status-indicator.inactive {
        color: #8a5b00;
        background: #fff2cc;
    }

    .user-security-note {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: 1rem;
        color: #33415c;
        background: #f5f8ff;
        border: 1px solid #dce5fb;
        border-radius: 7px;
    }

    .user-security-note i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        color: #435ebe;
        background: #e8edff;
        border-radius: 7px;
        font-size: 1rem;
        line-height: 1;
    }

    .user-form-actions {
        position: sticky;
        bottom: 0;
        z-index: 3;
        border-top: 1px solid #e8eaf1;
        box-shadow: 0 -5px 16px rgba(31, 45, 61, .05);
    }

    .user-form-card .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
    }

    .user-form-actions .btn {
        min-height: 42px;
        padding-inline: 1.25rem;
    }

    [data-bs-theme="dark"] .user-form-card,
    [data-bs-theme="dark"] .user-form-actions,
    [data-bs-theme="dark"] .user-form-section {
        border-color: #2b2b40;
    }

    [data-bs-theme="dark"] .user-form-actions,
    [data-bs-theme="dark"] .user-role-preview,
    [data-bs-theme="dark"] .user-security-note {
        background: #252539;
    }

    [data-bs-theme="dark"] .user-security-note i {
        background: #30304a;
    }

    [data-bs-theme="dark"] .user-form-card .form-label,
    [data-bs-theme="dark"] .user-security-note {
        color: #f5f7ff;
    }

    [data-bs-theme="dark"] .user-role-preview,
    [data-bs-theme="dark"] .user-security-note,
    [data-bs-theme="dark"] .user-status-option .form-check-label {
        border-color: #36364f;
    }

    [data-bs-theme="dark"] .user-status-option .form-check-input:checked + .form-check-label {
        background: #252539;
        border-color: #7185d5;
    }

    @media (max-width: 575.98px) {
        .user-form-section,
        .user-form-actions {
            padding: 1rem;
        }

        .user-status-options {
            grid-template-columns: 1fr;
        }

        .user-form-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .user-form-actions .d-flex {
            flex-direction: column-reverse;
        }

        .user-form-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="card user-form-card">
    <form action="<?= esc($formAction) ?>" method="post">
        <?= csrf_field() ?>

        <section class="user-form-section">
            <div class="user-form-section-heading">
                <span class="user-form-section-icon" aria-hidden="true"><i class="bi bi-person"></i></span>
                <div>
                    <h5>Informasi Profil</h5>
                    <p>Data identitas dan informasi kontak user.</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-7">
                    <label for="userName" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="userName" name="name" class="form-control" placeholder="Masukkan nama lengkap" autocomplete="name" value="<?= esc($oldValue('name', $user['name'] ?? '')) ?>" required>
                </div>
                <div class="col-md-5">
                    <label for="userUsername" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" id="userUsername" name="username" class="form-control" placeholder="Masukkan username" autocomplete="username" value="<?= esc($oldValue('username', $user['username'] ?? '')) ?>" required>
                    <div class="form-text">Digunakan user untuk masuk ke aplikasi.</div>
                </div>
                <div class="col-md-6">
                    <label for="userEmail" class="form-label">Email</label>
                    <input type="email" id="userEmail" name="email" class="form-control" placeholder="nama@perusahaan.com" autocomplete="email" value="<?= esc($oldValue('email', $user['email'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="userPhone" class="form-label">Nomor Telepon</label>
                    <input type="tel" id="userPhone" name="phone_number" class="form-control" placeholder="Contoh: 0812 3456 7890" autocomplete="tel" value="<?= esc($oldValue('phone_number', $user['phone_number'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="userJobTitle" class="form-label">Job Title</label>
                    <input type="text" id="userJobTitle" name="job_title" class="form-control" placeholder="Contoh: System Analyst" autocomplete="organization-title" value="<?= esc($oldValue('job_title', $user['job_title'] ?? '')) ?>">
                </div>
            </div>
        </section>

        <section class="user-form-section">
            <div class="user-form-section-heading">
                <span class="user-form-section-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                <div>
                    <h5>Akses & Status Akun</h5>
                    <p>Tentukan hak akses, kategori karyawan, dan ketersediaan akun.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-7">
                    <label for="userRole" class="form-label">Role Access <span class="text-danger">*</span></label>
                    <select id="userRole" name="role_id" class="form-select" required>
                        <option value="">Pilih role user</option>
                        <?php foreach ($roles as $role) : ?>
                            <option value="<?= esc($role['id']) ?>" <?= $selectedRoleId === (string) $role['id'] ? 'selected' : '' ?>>
                                <?= esc($role['role_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Role menentukan menu dan tindakan yang dapat diakses user.</div>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Kategori Karyawan</label>
                    <div class="user-role-preview" data-role-category-preview><?= esc($user['category'] ?? '-') ?></div>
                    <div class="form-text">Terisi otomatis berdasarkan role.</div>
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Status Akun <span class="text-danger">*</span></label>
                    <div class="user-status-options">
                        <div class="user-status-option">
                            <input class="form-check-input" type="radio" name="is_active" value="1" id="statusActive" <?= $selectedStatus === '1' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="statusActive">
                                <span class="user-status-indicator active" aria-hidden="true"><i class="bi bi-person-check-fill"></i></span>
                                <span>
                                    <span class="d-block fw-bold">Aktif</span>
                                    <span class="d-block small text-muted">User dapat masuk dan menggunakan aplikasi.</span>
                                </span>
                            </label>
                        </div>
                        <div class="user-status-option">
                            <input class="form-check-input" type="radio" name="is_active" value="0" id="statusInactive" <?= $selectedStatus === '0' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="statusInactive">
                                <span class="user-status-indicator inactive" aria-hidden="true"><i class="bi bi-person-dash-fill"></i></span>
                                <span>
                                    <span class="d-block fw-bold">Non-Aktif</span>
                                    <span class="d-block small text-muted">Akses login user dinonaktifkan sementara.</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="user-form-section">
            <div class="user-security-note">
                <i class="bi bi-key-fill" aria-hidden="true"></i>
                <div>
                    <div class="fw-bold mb-1"><?= $user ? 'Keamanan akun' : 'Password awal akun' ?></div>
                    <div class="small">
                        <?php if ($user) : ?>
                            Password hanya berubah melalui aksi Reset Password. Password reset sistem adalah <strong><?= esc($defaultPassword) ?></strong>.
                        <?php else : ?>
                            User baru akan menggunakan password awal sistem <strong><?= esc($defaultPassword) ?></strong> dan dapat menggantinya setelah login.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="user-form-actions">
            <span class="small text-muted">Pastikan role dan status akun sudah sesuai sebelum disimpan.</span>
            <div class="d-flex gap-2">
                <a href="<?= esc($cancelUrl) ?>" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy" aria-hidden="true"></i><?= esc($submitLabel) ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.user-form-card form');
    const roleSelect = form?.querySelector('[name="role_id"]');
    const preview = form?.querySelector('[data-role-category-preview]');
    const roleCategoryMap = <?= json_encode($roleCategoryMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    if (roleSelect && preview) {
        const syncCategory = () => {
            const selected = roleSelect.value;
            preview.textContent = roleCategoryMap[selected] || '-';
        };

        roleSelect.addEventListener('change', syncCategory);
        syncCategory();
    }
});
</script>
