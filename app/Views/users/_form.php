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
$defaultPassword = $defaultPassword ?? 'user123';

$oldValue = static function (string $field, $fallback = '') {
    $value = old($field);
    return $value !== null ? $value : $fallback;
};

$selectedRoleId = (string) $oldValue('role_id', (string) ($user['role_id'] ?? ''));
$selectedStatus = (string) $oldValue('is_active', (string) ($user['is_active'] ?? '1'));
?>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form action="<?= esc($formAction) ?>" method="post" novalidate>
            <?= csrf_field() ?>

            <div class="row g-4">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <div>
                            <h5 class="fw-bold mb-1">General Profile</h5>
                            <p class="text-muted mb-0">Isi data akun dasar user.</p>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="<?= esc($oldValue('name', $user['name'] ?? '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= esc($oldValue('username', $user['username'] ?? '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= esc($oldValue('email', $user['email'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Telepon</label>
                            <input type="text" name="phone_number" class="form-control" value="<?= esc($oldValue('phone_number', $user['phone_number'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Job Title</label>
                            <input type="text" name="job_title" class="form-control" value="<?= esc($oldValue('job_title', $user['job_title'] ?? '')) ?>">
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="border-top pt-4">
                        <h5 class="fw-bold mb-1">Roles & Status</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role Access</label>
                                <select name="role_id" class="form-select" required>
                                    <option value="">Pilih role</option>
                                    <?php foreach ($roles as $role) : ?>
                                        <option value="<?= esc($role['id']) ?>" <?= $selectedRoleId === (string) $role['id'] ? 'selected' : '' ?>>
                                            <?= esc($role['role_name']) ?> - <?= esc($role['category']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-block">Kategori Karyawan</label>
                                <div class="form-control-plaintext px-0 py-2 fw-semibold text-primary" data-role-category-preview>
                                    <?= esc($user['category'] ?? '-') ?>
                                </div>
                                <small class="text-muted">Kategori mengikuti role yang dipilih</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-block">Status Akun</label>
                                <div class="d-flex gap-3 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_active" value="1" id="statusActive" <?= $selectedStatus === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="statusActive">Aktif</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_active" value="0" id="statusInactive" <?= $selectedStatus === '0' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="statusInactive">Non-Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($user)) : ?>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            Password default sistem untuk reset adalah <strong><?= esc($defaultPassword) ?></strong>.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="<?= esc($cancelUrl) ?>" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><?= esc($submitLabel) ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.querySelector('[name="role_id"]');
    const preview = document.querySelector('[data-role-category-preview]');
    const roleCategoryMap = {
        <?php foreach ($roles as $role) : ?>
        "<?= esc((string) $role['id']) ?>": "<?= esc($role['category']) ?>",
        <?php endforeach; ?>
    };

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
