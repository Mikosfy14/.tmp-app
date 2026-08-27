<?php
/** @var array<string, mixed>|null $application */
/** @var array<int, array<string, mixed>> $criticalityOptions */
/** @var array<int, array<string, mixed>> $users */
/** @var string $formAction */
/** @var string $submitLabel */
$criticalityOptions = $criticalityOptions ?? [];
$users = $users ?? [];
$formAction = $formAction ?? '';
$submitLabel = $submitLabel ?? 'Simpan';
$application = $application ?? null;
$app = $application ?? [];
$value = static fn(string $key, string $default = '') => old($key, $app[$key] ?? $default);
$selects = [
    'app_type' => ['Application', 'Services'],
    'arch_type' => ['Monolithic', 'Microservices', 'Cloud', 'Hybrid'],
    'access_type' => ['Internal access', 'Public access', 'Public with internal'],
    'login_auth' => ['User AD', 'Non User AD', 'Userauth', 'MFA'],
    'platform' => ['Web app', 'Mobile', 'Desktop', 'API', 'Hybrid'],
    'development_type' => ['Internal', 'External', 'Both', 'COTS'],
    'license_scheme' => ['No license', 'Perpetual', 'Subscription'],
    'deployment_type' => ['On premise', 'Cloud', 'SaaS', 'Hybrid'],
];

// Preserve legacy database values that are not included in the recommended options.
$renderOptions = static function (string $field) use ($value, $selects): void {
    $current = (string) $value($field);
    if ($current !== '' && !in_array($current, $selects[$field], true)) {
        echo '<option selected>' . esc($current) . '</option>';
    }
    foreach ($selects[$field] as $option) {
        echo '<option value="' . esc($option, 'attr') . '"' . ($current === $option ? ' selected' : '') . '>' . esc($option) . '</option>';
    }
};
?>

<link rel="stylesheet" href="<?= base_url('assets/vendors/choices.js/choices.css') ?>">
<style>
    .application-form-wrap {
        max-width: 1180px;
    }

    .application-form-card {
        border: 0;
        overflow: visible;
        position: relative;
    }

    .application-form-card .card-header {
        background: transparent;
        border-bottom: 1px solid var(--bs-border-color);
        padding: 1.35rem 1.5rem;
    }

    .application-section-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .75rem;
    }

    .application-section-icon i,
    .application-form-wrap .btn i,
    .application-form-wrap .input-group-text i {
        line-height: 1;
        vertical-align: middle;
    }

    .application-form-card .form-label {
        font-weight: 700;
        margin-bottom: .45rem;
    }

    .application-form-card .form-text {
        font-size: .78rem;
    }

    .application-form-card .form-control,
    .application-form-card .form-select {
        min-height: 44px;
    }

    .application-form-card textarea.form-control {
        min-height: 108px;
    }

    .application-action-bar {
        position: sticky;
        bottom: 1rem;
        z-index: 20;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: .85rem;
        box-shadow: 0 .5rem 1.5rem rgba(34, 41, 47, .08);
        padding: .85rem 1rem;
        margin-bottom: 1.5rem;
    }

    .application-action-buttons {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: .75rem;
    }

    @media (max-width: 575.98px) {
        .application-action-bar {
            bottom: .5rem;
            padding: .75rem;
        }

        .application-action-buttons,
        .application-action-buttons .btn {
            width: 100%;
        }

        .application-action-buttons .btn {
            justify-content: center;
        }
    }

    .source-code-option {
        border: 1px solid var(--bs-border-color);
        border-radius: .75rem;
        padding: 1rem;
        background: var(--bs-tertiary-bg);
    }

    #assignedPicField .choices {
        margin-bottom: 0;
    }

    #assignedPicField {
        position: relative;
        z-index: 40;
    }

    #assignedPicField .choices__inner {
        min-height: 44px;
        padding: .45rem .75rem;
        border-color: var(--bs-border-color);
        border-radius: .35rem;
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    #assignedPicField .choices.is-focused .choices__inner,
    #assignedPicField .choices.is-open .choices__inner {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .15);
    }

    #assignedPicField .choices__input,
    #assignedPicField .choices__list--dropdown {
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    #assignedPicField .choices__list--dropdown {
        z-index: 50;
        border-color: var(--bs-border-color);
        max-height: 260px;
        overflow-y: auto;
    }

    #assignedPicField .choices__item--choice.is-highlighted {
        background: var(--bs-tertiary-bg);
    }
</style>

<form action="<?= esc($formAction) ?>" method="post" class="application-form-wrap mx-auto">
    <?= csrf_field() ?>

    <div class="card shadow-sm application-form-card mb-4">
        <div class="card-header d-flex gap-3 align-items-center"><span class="application-section-icon bg-light-primary text-primary"><i class="bi bi-grid-1x2 fs-5"></i></span>
            <div>
                <h5 class="mb-1">Informasi Utama</h5>
                <p class="text-muted mb-0 text-sm">Identitas dan klasifikasi dasar aplikasi.</p>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-lg-8"><label for="appComponent" class="form-label">Nama Aplikasi / App Component <span class="text-danger">*</span></label><input id="appComponent" name="app_component" maxlength="150" required autofocus class="form-control" placeholder="Contoh: Portal HRD & Absensi" value="<?= esc($value('app_component')) ?>">
                    <div class="form-text">Gunakan nama yang mudah dikenali oleh pengguna dan tim pengelola.</div>
                </div>
                <div class="col-lg-4"><label for="criticality" class="form-label">Criticality Recovery <span class="text-danger">*</span></label><select id="criticality" name="criticality_recovery_id" required class="form-select">
                        <option value="">Pilih tingkat criticality</option><?php foreach ($criticalityOptions as $item) : ?><option value="<?= (int) $item['id'] ?>" <?= (string) $value('criticality_recovery_id') === (string) $item['id'] ? 'selected' : '' ?>><?= esc((string) ($item['criticality_name'] ?? '') . ' - ' . (string) ($item['description'] ?? '')) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="col-12"><label for="description" class="form-label">Deskripsi</label><textarea id="description" name="description" class="form-control" placeholder="Jelaskan fungsi utama, pengguna, dan proses bisnis yang didukung."><?= esc($value('description')) ?></textarea></div>
                <?php foreach (['app_type' => 'Tipe Aplikasi', 'arch_type' => 'Arsitektur', 'platform' => 'Platform'] as $name => $label) : ?><div class="col-md-4"><label for="<?= $name ?>" class="form-label"><?= $label ?></label><select id="<?= $name ?>" name="<?= $name ?>" class="form-select">
                            <option value="">Pilih <?= strtolower($label) ?></option><?php $renderOptions($name); ?>
                        </select></div><?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm application-form-card mb-4">
        <div class="card-header d-flex gap-3 align-items-center"><span class="application-section-icon bg-light-info text-info"><i class="bi bi-diagram-2 fs-5"></i></span>
            <div>
                <h5 class="mb-1">Akses & Environment</h5>
                <p class="text-muted mb-0 text-sm">Cara pengguna mengakses aplikasi dan alamat setiap environment.</p>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <?php foreach (['access_type' => 'Tipe Akses', 'login_auth' => 'Autentikasi Login'] as $name => $label) : ?><div class="col-md-6"><label for="<?= $name ?>" class="form-label"><?= $label ?></label><select id="<?= $name ?>" name="<?= $name ?>" class="form-select">
                            <option value="">Pilih <?= strtolower($label) ?></option><?php $renderOptions($name); ?>
                        </select></div><?php endforeach; ?>
                <div class="col-12">
                    <hr class="my-0">
                    <p class="text-muted text-sm mt-3 mb-0">Isi URL lengkap beserta protokol, misalnya <code>https://aplikasi.perusahaan.co.id</code>.</p>
                </div>
                <?php foreach (['url_prod' => ['URL Production', 'Alamat aplikasi yang digunakan pengguna'], 'url_dev' => ['URL Development', 'Alamat environment pengembangan'], 'url_uat' => ['URL UAT', 'Alamat environment user acceptance test']] as $name => [$label, $help]) : ?><div class="col-lg-4"><label for="<?= $name ?>" class="form-label"><?= $label ?></label>
                        <div class="input-group"><span class="input-group-text"><i class="bi bi-globe2"></i></span><input id="<?= $name ?>" type="url" name="<?= $name ?>" class="form-control" placeholder="https://" value="<?= esc($value($name)) ?>"></div>
                        <div class="form-text"><?= $help ?></div>
                    </div><?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm application-form-card mb-4">
        <div class="card-header d-flex gap-3 align-items-center"><span class="application-section-icon bg-light-warning text-warning"><i class="bi bi-briefcase fs-5"></i></span>
            <div>
                <h5 class="mb-1">Pengelolaan & Kepemilikan</h5>
                <p class="text-muted mb-0 text-sm">Model pengembangan, deployment, owner, dan PIC aplikasi.</p>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <?php foreach (['development_type' => 'Tipe Pengembangan', 'license_scheme' => 'Skema Lisensi', 'deployment_type' => 'Tipe Deployment'] as $name => $label) : ?><div class="col-md-4"><label for="<?= $name ?>" class="form-label"><?= $label ?></label><select id="<?= $name ?>" name="<?= $name ?>" class="form-select">
                            <option value="">Pilih <?= strtolower($label) ?></option><?php $renderOptions($name); ?>
                        </select></div><?php endforeach; ?>
                <div class="col-md-4"><label for="vendor" class="form-label">Vendor</label><input id="vendor" name="vendor" class="form-control" placeholder="Nama vendor, jika ada" value="<?= esc($value('vendor')) ?>"></div>
                <div class="col-md-4"><label for="businessOwner" class="form-label">Business Owner</label><input id="businessOwner" name="business_owner" class="form-control" placeholder="Unit pemilik proses bisnis" value="<?= esc($value('business_owner')) ?>"></div>
                <div class="col-md-4"><label for="systemOwner" class="form-label">System Owner</label><input id="systemOwner" name="system_owner" class="form-control" placeholder="Unit pemilik sistem" value="<?= esc($value('system_owner')) ?>"></div>
                <div class="col-lg-7" id="assignedPicField"><label for="assignedPic" class="form-label">Assigned PIC</label><select id="assignedPic" name="assigned_user_id" class="form-select" data-placeholder="Cari dan pilih PIC">
                        <option value="">---</option><?php foreach ($users as $user) : ?><option value="<?= (int) $user['id'] ?>" <?= (string) $value('assigned_user_id') === (string) $user['id'] ? 'selected' : '' ?>><?= esc((string) ($user['name'] ?? '')) ?><?= !empty($user['job_title']) ? ' - ' . esc((string) $user['job_title']) : '' ?></option><?php endforeach; ?>
                    </select>
                    <div class="form-text">PIC bertanggung jawab atas koordinasi pengelolaan aplikasi.</div>
                </div>
                <div class="col-lg-5"><label class="form-label">Ketersediaan Source Code</label>
                    <div class="source-code-option">
                        <div class="form-check form-switch mb-1"><input type="checkbox" name="has_source_code" value="1" class="form-check-input" id="hasSourceCode" <?= (int) $value('has_source_code', '0') === 1 ? 'checked' : '' ?>><label for="hasSourceCode" class="form-check-label fw-semibold">Source code tersedia</label></div><small class="text-muted">Aktifkan jika source code dimiliki atau dapat diakses tim.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="application-action-bar">
        <div class="application-action-buttons">
            <a href="<?= base_url('/aplikasi') ?>" class="btn btn-outline-secondary px-4">Batal</a>
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i><?= esc($submitLabel) ?></button>
        </div>
    </div>
</form>

<script src="<?= base_url('assets/vendors/choices.js/choices.min.js') ?>"></script>
<script>
    (() => {
        const assignedPic = document.getElementById('assignedPic');

        if (!assignedPic || typeof window.Choices !== 'function') {
            return;
        }

        const choices = new window.Choices(assignedPic, {
            allowHTML: false,
            searchEnabled: true,
            searchChoices: true,
            searchResultLimit: -1,
            renderChoiceLimit: -1,
            shouldSort: false,
            itemSelectText: 'Pilih',
            noResultsText: 'PIC tidak ditemukan',
            noChoicesText: 'Tidak ada PIC tersedia',
            searchPlaceholderValue: 'Ketik nama atau jabatan PIC...',
            placeholder: false,
        });

        // Keep the optional empty assignment accessible regardless of the search query.
        const keepUnassignedChoiceFirst = () => {
            window.requestAnimationFrame(() => {
                const dropdown = document.querySelector('#assignedPicField .choices__list--dropdown');
                const choiceList = dropdown?.querySelector('.choices__list');
                const unassignedChoice = choiceList?.querySelector('[data-choice][data-value=""]');

                if (!choiceList || !unassignedChoice) {
                    return;
                }

                unassignedChoice.hidden = false;
                unassignedChoice.setAttribute('aria-hidden', 'false');
                unassignedChoice.style.display = '';
                unassignedChoice.classList.remove('is-hidden');
                choiceList.prepend(unassignedChoice);

                const noResultsNotice = choiceList.querySelector('.has-no-results');
                if (noResultsNotice) {
                    noResultsNotice.classList.add('d-none');
                }
            });
        };

        assignedPic.addEventListener('search', keepUnassignedChoiceFirst);
        assignedPic.addEventListener('showDropdown', keepUnassignedChoiceFirst);

        if (choices) {
            keepUnassignedChoiceFirst();
        }
    })();
</script>
