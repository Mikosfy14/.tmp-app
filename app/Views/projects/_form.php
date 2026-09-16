<?php

/**
 * @var array $statusOptions
 * @var array $databaseTypeOptions
 * @var array $users
 * @var array|null $project
 * @var array $selectedAssignedIds
 * @var array $projectFiles
 * @var string $formAction
 * @var string $submitLabel
 * @var string $cardTitle
 */

$project = $project ?? null;
$projectFiles = $projectFiles ?? [];
$databaseTypeOptions = $databaseTypeOptions ?? [];
$selectedAssignedIds = array_values(array_filter(array_map('intval', $selectedAssignedIds ?? [])));
$selectedAssignedIds = !empty($selectedAssignedIds) ? $selectedAssignedIds : [(int) session()->get('user_id')];
$responsibleAssignedId = $project
    ? (int) (explode(',', (string) ($project['assigned_to'] ?? ''))[0] ?? 0)
    : (int) session()->get('user_id');
$responsibleAssignedId = $responsibleAssignedId > 0 ? $responsibleAssignedId : (int) session()->get('user_id');
$isKadept = strtolower((string) session()->get('role_name')) === 'kepala departemen' || (int) session()->get('role_id') === 1;
$canManageAssignees = empty($project) || ((int) session()->get('user_id') === (int) $responsibleAssignedId) || $isKadept;
$oldAssigned = old('assigned_to');
if (is_array($oldAssigned)) {
    $selectedAssignedIds = array_values(array_filter(array_map('intval', $oldAssigned)));
} elseif (is_string($oldAssigned) && $oldAssigned !== '') {
    $selectedAssignedIds = array_values(array_filter(array_map('intval', explode(',', $oldAssigned))));
}

$value = static function (string $field, $default = '') {
    $old = old($field);
    if ($old !== null && $old !== '') {
        return $old;
    }

    return $default;
};

$isSelected = static function (int $userId) use ($selectedAssignedIds): bool {
    return in_array($userId, $selectedAssignedIds, true);
};

$primaryAssignedUser = null;
foreach ($users as $user) {
    if ((int) ($user['id'] ?? 0) === $responsibleAssignedId) {
        $primaryAssignedUser = $user;
        break;
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="<?= base_url('assets/vendors/choices.js/choices.css') ?>">
<style>
    .project-form-card {
        max-width: 1120px;
        margin-inline: auto;
        overflow: visible;
        border: 1px solid #e8eaf1;
        box-shadow: 0 8px 28px rgba(31, 45, 61, .07) !important;
    }

    .project-form-page-header {
        max-width: 1120px;
        margin: 0 auto 1.25rem;
    }

    .project-form-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: #435ebe;
        background: #eef1ff;
    }

    .project-form-section-icon i,
    .project-form-upload-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .project-form-section {
        padding: 1.5rem;
        border-bottom: 1px solid #edf0f5;
    }

    .project-form-section:last-of-type {
        border-bottom: 0;
    }

    .project-form-section-heading {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        margin-bottom: 1.25rem;
    }

    .project-form-section-icon {
        width: 34px;
        height: 34px;
        border-radius: 7px;
        font-size: .95rem;
    }

    .project-form-section-heading h5 {
        margin: 0 0 .15rem;
        font-size: 1rem;
        font-weight: 700;
    }

    .project-form-section-heading p {
        margin: 0;
        font-size: .875rem;
        color: #7c8193;
    }

    .project-form-card .form-label {
        margin-bottom: .45rem;
        color: #363b4e;
        font-size: .875rem;
        font-weight: 700;
    }

    .project-form-card .form-control,
    .project-form-card .form-select {
        min-height: 44px;
        border-color: #dfe3eb;
        border-radius: 6px;
    }

    .project-form-card textarea.form-control {
        min-height: 128px;
        resize: vertical;
    }

    .project-form-card .form-control:focus,
    .project-form-card .form-select:focus,
    #assignedToChoices .is-focused .choices__inner,
    #assignedToChoices .is-open .choices__inner {
        border-color: #7185d5;
        box-shadow: 0 0 0 .2rem rgba(67, 94, 190, .12);
    }

    .project-form-card .form-text {
        margin-top: .45rem;
        line-height: 1.45;
    }

    .project-form-card .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
    }

    .project-form-card .btn i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .project-form-upload {
        padding: 1rem;
        background: #fafbfe;
        border: 1px dashed #bdc5d8;
        border-radius: 7px;
    }

    .project-form-upload-icon {
        color: #435ebe;
        font-size: 1.4rem;
    }

    .project-form-actions {
        position: sticky;
        bottom: 0;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        background: #f8f9fc;
        border-top: 1px solid #e8eaf1;
        box-shadow: 0 -5px 16px rgba(31, 45, 61, .05);
    }

    .project-form-actions .btn {
        min-height: 42px;
        padding-inline: 1.25rem;
    }

    #assignedToChoices {
        position: relative;
        z-index: 40;
    }

    #assignedToChoices .choices {
        margin-bottom: 0;
    }

    #assignedToChoices .choices__inner {
        min-height: 44px;
        padding: .45rem .75rem;
        border-color: var(--bs-border-color);
        border-radius: .35rem;
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    #assignedToChoices .choices.is-focused .choices__inner,
    #assignedToChoices .choices.is-open .choices__inner {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .15);
    }

    #assignedToChoices .choices__input,
    #assignedToChoices .choices__list--dropdown {
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    #assignedToChoices .choices__list--dropdown {
        z-index: 50;
        border-color: var(--bs-border-color);
        overflow: hidden;
    }

    #assignedToChoices .choices__list--dropdown .choices__list {
        max-height: 260px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    #assignedToChoices .choices__item--choice.is-highlighted {
        background: var(--bs-tertiary-bg);
    }

    .project-pic-readonly {
        min-height: 44px;
        padding: .5rem .85rem;
        background: #f5f6fa;
        border: 1px solid #e2e5ed;
        border-radius: 6px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem;
    }

    #selectedProjectFiles,
    .project-existing-files {
        max-height: 280px;
        overflow-y: auto;
        padding-right: .25rem;
    }

    .project-file-item {
        transition: background-color .15s ease-in-out;
    }

    .project-file-item:hover {
        background-color: var(--bs-tertiary-bg);
    }

    .project-file-item.is-selected {
        background-color: rgba(67, 94, 190, .06);
        border-color: rgba(67, 94, 190, .25);
    }

    [data-bs-theme="dark"] .project-file-item.is-selected {
        background-color: rgba(67, 94, 190, .18);
        border-color: rgba(67, 94, 190, .45);
    }

    #selectedProjectFiles:empty,
    .project-existing-files:empty {
        display: none;
    }

    #assignedToChoices .choices__list--multiple .choices__item {
        background-color: #e9ecef;
        border-color: #ced4da;
        color: #212529;
        border-radius: .3rem;
        font-weight: 500;
    }

    #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"] {
        background-color: #343a40;
        border-color: #212529;
        color: #ffffff;
        font-weight: 700;
    }

    #assignedToChoices .choices__list--multiple .choices__item.is-highlighted,
    #assignedToChoices .choices__list--multiple .choices__item:hover {
        background-color: #dee2e6;
        border-color: #adb5bd;
        color: #212529;
    }

    #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"]:hover,
    #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"].is-highlighted {
        background-color: #212529;
        border-color: #1a1e21;
        color: #ffffff;
    }

    #assignedToChoices .choices__list--multiple .choices__button {
        border-left-color: #ced4da;
        filter: invert(1) grayscale(100%) brightness(20%);
    }

    #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"] .choices__button {
        display: none;
    }

    [data-bs-theme="dark"] #assignedToChoices .choices__list--multiple .choices__item {
        background-color: #2b2b40;
        border-color: #3f3f5a;
        color: #f5f7ff;
    }

    [data-bs-theme="dark"] #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"] {
        background-color: #495057;
        border-color: #6c757d;
        color: #ffffff;
    }

    [data-bs-theme="dark"] #assignedToChoices .choices__list--multiple .choices__item.is-highlighted,
    [data-bs-theme="dark"] #assignedToChoices .choices__list--multiple .choices__item:hover {
        background-color: #383852;
        border-color: #525275;
        color: #ffffff;
    }

    [data-bs-theme="dark"] #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"]:hover,
    [data-bs-theme="dark"] #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"].is-highlighted {
        background-color: #343a40;
        border-color: #495057;
        color: #ffffff;
    }

    [data-bs-theme="dark"] #assignedToChoices .choices__list--multiple .choices__button {
        border-left-color: #3f3f5a;
        filter: none;
    }

    .flatpickr-input[readonly] {
        background-color: var(--bs-body-bg);
        cursor: pointer;
    }

    [data-bs-theme="dark"] .flatpickr-calendar,
    [data-bs-theme="dark"] .flatpickr-months .flatpickr-month,
    [data-bs-theme="dark"] .flatpickr-weekdays,
    [data-bs-theme="dark"] span.flatpickr-weekday {
        background: #1e1e2d;
        color: #f5f7ff;
    }

    [data-bs-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months,
    [data-bs-theme="dark"] .flatpickr-current-month input.cur-year,
    [data-bs-theme="dark"] .flatpickr-day {
        color: #e6eaee;
    }

    [data-bs-theme="dark"] .flatpickr-day:hover,
    [data-bs-theme="dark"] .flatpickr-day:focus {
        background: #2b2b40;
        border-color: #2b2b40;
    }

    [data-bs-theme="dark"] .flatpickr-day.selected {
        background: #435ebe;
        border-color: #435ebe;
        color: #ffffff;
    }

    [data-bs-theme="dark"] .project-form-card,
    [data-bs-theme="dark"] .project-form-actions,
    [data-bs-theme="dark"] .project-form-section {
        border-color: #2b2b40;
    }

    [data-bs-theme="dark"] .project-form-actions,
    [data-bs-theme="dark"] .project-form-upload,
    [data-bs-theme="dark"] .project-pic-readonly {
        background: #252539;
    }

    [data-bs-theme="dark"] .project-pic-readonly {
        border-color: #36364f;
    }

    [data-bs-theme="dark"] .project-form-card .form-label {
        color: #f5f7ff;
    }

    @media (max-width: 767.98px) {

        .project-form-section {
            padding: 1.15rem 1rem;
        }

        .project-form-actions {
            padding: 0.85rem 1rem;
            align-items: stretch;
            flex-direction: column;
            gap: 0.75rem;
            z-index: 1030;
        }

        .project-form-actions .small.text-muted {
            text-align: center;
            font-size: 0.78rem;
            order: 2;
        }

        .project-form-actions .d-flex {
            width: 100%;
            gap: 0.5rem;
            order: 1;
        }

        .project-form-actions .btn {
            flex: 1 1 0 !important;
            width: 50% !important;
            min-height: 44px;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            box-sizing: border-box;
            text-align: center;
            font-size: 0.875rem;
        }

        .project-form-actions .btn-primary i {
            display: none !important;
        }

        .project-file-item .d-flex.flex-wrap.gap-2.flex-shrink-0 {
            width: 100%;
        }

        .project-file-item .d-flex.flex-wrap.gap-2.flex-shrink-0 .btn {
            flex: 1 1 45%;
            justify-content: center;
            min-height: 38px;
        }
    }
</style>

<div class="card project-form-card mb-4">
    <form action="<?= esc($formAction) ?>" method="POST" enctype="multipart/form-data" autocomplete="off">
        <?= csrf_field() ?>

        <section class="project-form-section">
            <div class="project-form-section-heading">
                <span class="project-form-section-icon" aria-hidden="true"><i class="bi bi-card-text"></i></span>
                <div>
                    <h5>Informasi Utama</h5>
                    <p>Identitas dasar dan posisi project dalam tahapan SDLC.</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="projectCode" class="form-label">Project Code <span class="text-danger">*</span></label>
                    <input type="text" id="projectCode" name="project_code" class="form-control" maxlength="50" placeholder="Contoh: PRJ-2026-001" value="<?= esc($value('project_code', $project['project_code'] ?? '')) ?>" required>
                    <div class="form-text">Gunakan kode unik yang mudah dikenali.</div>
                </div>
                <div class="col-md-8">
                    <label for="projectName" class="form-label">Nama Project <span class="text-danger">*</span></label>
                    <input type="text" id="projectName" name="name" class="form-control" maxlength="250" placeholder="Masukkan nama project" value="<?= esc($value('name', $project['name'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="projectStatus" class="form-label">Status SDLC <span class="text-danger">*</span></label>
                    <select id="projectStatus" name="project_status_id" class="form-select" required>
                        <option value="">Pilih status project</option>
                        <?php
                        $currentStatusId = (int) ($value('project_status_id', $project['project_status_id'] ?? 0));
                        foreach ($statusOptions as $st) : ?>
                            <option value="<?= esc($st['id']) ?>" <?= $currentStatusId === (int) $st['id'] ? 'selected' : '' ?>>
                                <?= esc($st['status_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="databaseType" class="form-label">Tipe Database <span class="text-danger">*</span></label>
                    <select id="databaseType" name="database_type_id" class="form-select" required>
                        <option value="">Pilih tipe database</option>
                        <?php
                        $currentDbId = (int) ($value('database_type_id', $project['database_type_id'] ?? 0));
                        foreach ($databaseTypeOptions as $dbOpt) : ?>
                            <option value="<?= esc($dbOpt['id']) ?>" <?= $currentDbId === (int) $dbOpt['id'] ? 'selected' : '' ?>>
                                <?= esc($dbOpt['type_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="assignedTo" class="form-label">Assigned To / PIC</label>
                    <?php if ($canManageAssignees) : ?>
                        <div id="assignedToChoices" data-primary-id="<?= (int) $responsibleAssignedId ?>">
                            <select id="assignedTo" name="assigned_to[]" class="form-select" multiple data-placeholder="Cari dan pilih PIC">
                                <?php foreach ($users as $user) : ?>
                                    <option value="<?= (int) $user['id'] ?>" <?= $isSelected((int) $user['id']) ? 'selected' : '' ?>>
                                        <?= esc((string) ($user['name'] ?? '')) ?><?= !empty($user['job_title']) ? ' - ' . esc((string) $user['job_title']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else : ?>
                        <div class="project-pic-readonly">
                            <?php
                            $assignedUserList = [];
                            foreach ($users as $u) {
                                if ($isSelected((int) $u['id'])) {
                                    $assignedUserList[] = $u;
                                }
                            }
                            usort($assignedUserList, static function ($a, $b) use ($responsibleAssignedId) {
                                if ((int) $a['id'] === $responsibleAssignedId) return -1;
                                if ((int) $b['id'] === $responsibleAssignedId) return 1;
                                return 0;
                            });
                            ?>
                            <?php if (!empty($assignedUserList)) : ?>
                                <?php foreach ($assignedUserList as $assignee) : ?>
                                    <?php $isPrimary = ((int) $assignee['id'] === $responsibleAssignedId); ?>
                                    <span class="badge <?= $isPrimary ? 'bg-light-success text-success border border-success-subtle' : 'bg-light-secondary text-secondary border border-secondary-subtle' ?> py-1 px-2.5 d-inline-flex align-items-center gap-1" style="font-size: 0.8rem; font-weight: 500;">
                                        <?php if ($isPrimary) : ?>
                                            <i class="bi bi-star-fill text-success" aria-hidden="true"></i>
                                        <?php else : ?>
                                            <i class="bi bi-person text-secondary" aria-hidden="true"></i>
                                        <?php endif; ?>
                                        <span><?= esc((string) ($assignee['name'] ?? '')) ?><?= !empty($assignee['job_title']) ? ' (' . esc((string) $assignee['job_title']) . ')' : '' ?></span>
                                        <?php if ($isPrimary) : ?>
                                            <span class="badge bg-success text-white ms-1 py-0 px-1" style="font-size: 0.65rem;">Penanggung Jawab</span>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <span class="text-muted small">Tidak ada PIC yang ditugaskan.</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($primaryAssignedUser) : ?>
                        <div class="form-text">
                            <i class="bi bi-person-check-fill me-1 text-success" aria-hidden="true"></i>
                            <?= $project ? ($canManageAssignees ? 'PIC utama dipertahankan saat project diedit.' : 'Hanya PIC penanggung jawab yang berhak mengubah susunan tim.') : 'Pembuat project otomatis menjadi PIC utama.' ?>
                        </div>
                    <?php else : ?>
                        <div class="form-text">Pembuat project otomatis menjadi PIC utama.</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="project-form-section">
            <div class="project-form-section-heading">
                <span class="project-form-section-icon" aria-hidden="true"><i class="bi bi-calendar3"></i></span>
                <div>
                    <h5>Timeline Project</h5>
                    <p>Tentukan periode utama dan target setiap milestone pelaksanaan.</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="startDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="text" id="startDate" name="start_date" class="form-control bg-white" placeholder="Pilih tanggal mulai..." value="<?= esc($value('start_date', $project['start_date'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="endDate" class="form-label">End Date <span class="text-danger">*</span></label>
                    <input type="text" id="endDate" name="end_date" class="form-control bg-white" placeholder="Pilih tanggal selesai..." value="<?= esc($value('end_date', $project['end_date'] ?? '')) ?>" required>
                </div>
                <div class="col-12 mt-4">
                    <div class="small fw-bold text-muted text-uppercase">Target Milestone</div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="unitTestingDate" class="form-label">Unit Testing</label>
                    <input type="text" id="unitTestingDate" name="unit_testing_date" class="form-control bg-white" placeholder="Pilih tanggal..." value="<?= esc($value('unit_testing_date', $project['unit_testing_date'] ?? '')) ?>">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="sitDate" class="form-label">SIT</label>
                    <input type="text" id="sitDate" name="sit_date" class="form-control bg-white" placeholder="Pilih tanggal..." value="<?= esc($value('sit_date', $project['sit_date'] ?? '')) ?>">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="uatDate" class="form-label">UAT</label>
                    <input type="text" id="uatDate" name="uat_date" class="form-control bg-white" placeholder="Pilih tanggal..." value="<?= esc($value('uat_date', $project['uat_date'] ?? '')) ?>">
                </div>
                <?php
                $isCurrentDeployment = false;
                foreach ($statusOptions as $st) {
                    if ((int) $st['id'] === $currentStatusId) {
                        $sName = strtolower((string) ($st['status_name'] ?? ''));
                        if (str_contains($sName, 'deployment') || str_contains($sName, 'complete') || str_contains($sName, 'selesai') || str_contains($sName, 'done')) {
                            $isCurrentDeployment = true;
                        }
                        break;
                    }
                }
                ?>
                <div class="col-sm-6 col-lg-3">
                    <label for="promoteDate" class="form-label">
                        Promote <span id="promoteRequiredAsterisk" class="text-danger <?= $isCurrentDeployment ? '' : 'd-none' ?>">*</span>
                    </label>
                    <input type="text" id="promoteDate" name="promote_date" class="form-control bg-white" placeholder="Pilih tanggal..." value="<?= esc($value('promote_date', $project['promote_date'] ?? '')) ?>" <?= $isCurrentDeployment ? 'required' : '' ?>>
                    <div id="promoteHelpText" class="form-text text-danger <?= $isCurrentDeployment ? '' : 'd-none' ?>" style="font-size: 0.72rem;">
                        Wajib diisi saat status Deployment.
                    </div>
                </div>
            </div>
        </section>

        <section class="project-form-section">
            <div class="project-form-section-heading">
                <span class="project-form-section-icon" aria-hidden="true"><i class="bi bi-paperclip"></i></span>
                <div>
                    <h5>Catatan & Lampiran</h5>
                    <p>Tambahkan konteks project dan dokumen pendukung yang diperlukan.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-7">
                    <label for="projectNotes" class="form-label">Notes</label>
                    <textarea id="projectNotes" name="notes" class="form-control" rows="5" placeholder="Tuliskan ruang lingkup, kebutuhan, atau catatan penting project..."><?= esc($value('notes', $project['notes'] ?? '')) ?></textarea>
                </div>
                <div class="col-lg-5">
                    <label for="projectFilesInput" class="form-label">File Project</label>
                    <div class="project-form-upload">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-cloud-arrow-up project-form-upload-icon" aria-hidden="true"></i>
                            <div>
                                <div class="fw-semibold small">Upload dokumen pendukung</div>
                                <div class="text-muted small">PDF, Word, atau Excel</div>
                            </div>
                        </div>
                        <input type="file" id="projectFilesInput" name="project_files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.xls,.xlsx">
                        <div class="form-text mb-0">Maksimal 5 MB untuk setiap file.</div>
                    </div>
                    <div id="selectedProjectFiles" class="list-group mt-3 d-none" aria-live="polite"></div>
                </div>
            </div>

            <?php if (!empty($project) && !empty($projectFiles)) : ?>
                <div class="mt-4 pt-4 border-top" id="existingFilesContainer">
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold mb-0">File Tersimpan</h6>
                            <span class="badge bg-light-primary text-primary"><?= count($projectFiles) ?> file</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="selectAllProjectFiles">
                                <label class="form-check-label small fw-semibold user-select-none" for="selectAllProjectFiles">
                                    Pilih Semua
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Actions Toolbar (Visible when 1 or more files are checked) -->
                    <div id="bulkFileActionsBar" class="alert alert-light-primary border border-primary-subtle d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 p-2 px-3 mb-3 d-none">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-bold text-dark">
                                <span id="selectedFilesCount">0</span> file dipilih
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" id="btnBulkDownloadFiles">
                                <span>Download File Terpilih</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1" id="btnBulkDeleteFilesModal" data-bs-toggle="modal" data-bs-target="#modalBulkDeleteProjectFiles">
                                <span>Hapus File Terpilih</span>
                            </button>
                        </div>
                    </div>

                    <div class="list-group project-existing-files">
                        <?php foreach ($projectFiles as $file) : ?>
                            <div class="list-group-item project-file-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" data-file-id="<?= (int) $file['id'] ?>">
                                <div class="d-flex align-items-center gap-3 min-width-0">
                                    <div class="form-check mb-0 flex-shrink-0">
                                        <input class="form-check-input project-file-checkbox" type="checkbox" value="<?= (int) $file['id'] ?>" id="checkProjectFile<?= (int) $file['id'] ?>" aria-label="Pilih <?= esc($file['original_name']) ?>">
                                    </div>
                                    <span class="project-form-section-icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span>
                                    <div class="min-width-0">
                                        <label class="fw-semibold text-break mb-0 d-block user-select-none cursor-pointer" for="checkProjectFile<?= (int) $file['id'] ?>"><?= esc($file['original_name']) ?></label>
                                        <small class="text-muted">
                                            <?= esc(number_format(((int) ($file['file_size'] ?? 0)) / 1024, 1)) ?> KB
                                            <?php if (!empty($file['uploaded_by_name'])) : ?>
                                                &middot; <?= esc($file['uploaded_by_name']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                                    <a href="<?= base_url('/projects/files/' . (int) $file['id'] . '/download') ?>" class="btn btn-sm btn-outline-primary" title="Download file ini">
                                        <i class="bi bi-download" aria-hidden="true"></i> Download
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteProjectFile<?= (int) $file['id'] ?>" title="Hapus file ini">
                                        <i class="bi bi-trash" aria-hidden="true"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif (!empty($project)) : ?>
                <div class="alert alert-light border mt-4 mb-0">
                    Belum ada file yang tersimpan untuk project ini.
                </div>
            <?php endif; ?>
        </section>

        <div class="project-form-actions">
            <span class="small text-muted">Pastikan informasi project sudah sesuai sebelum disimpan.</span>
            <div class="d-flex gap-2">
                <a href="<?= base_url('/projects') ?>" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg d-none d-md-inline me-1" aria-hidden="true"></i><?= esc($submitLabel) ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php if (!empty($projectFiles)) : ?>
    <!-- Bulk Delete Confirmation Modal -->
    <div class="modal fade" id="modalBulkDeleteProjectFiles" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="mb-1">
                        Apakah kamu yakin ingin menghapus <strong id="bulkDeleteFilesText">0 file</strong> terpilih?
                    </p>
                    <small class="text-danger">File yang dihapus tidak dapat dipulihkan kembali.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmBulkDelete">
                        Hapus File Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for bulk download -->
    <form id="formBulkDownloadProjectFiles" action="<?= base_url('/projects/files/bulk-download') ?>" method="POST" class="d-none">
        <?= csrf_field() ?>
        <input type="hidden" name="project_id" value="<?= (int) ($project['id'] ?? 0) ?>">
        <div id="bulkDownloadInputsContainer"></div>
    </form>

    <!-- Hidden form for bulk delete -->
    <form id="formBulkDeleteProjectFiles" action="<?= base_url('/projects/files/bulk-delete') ?>" method="POST" class="d-none">
        <?= csrf_field() ?>
        <input type="hidden" name="project_id" value="<?= (int) ($project['id'] ?? 0) ?>">
        <div id="bulkDeleteInputsContainer"></div>
    </form>

    <?php foreach ($projectFiles as $file) : ?>
        <div class="modal fade" id="modalDeleteProjectFile<?= (int) $file['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        Apakah kamu yakin ingin menghapus file <strong><?= esc($file['original_name']) ?></strong>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <form action="<?= base_url('/projects/files/delete/' . (int) $file['id']) ?>" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger">Hapus File</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    (() => {
        const input = document.getElementById('projectFilesInput');
        const list = document.getElementById('selectedProjectFiles');
        const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        const maxSize = 5 * 1024 * 1024;

        if (!input || !list) {
            return;
        }

        let selectedFiles = [];

        const formatSize = (bytes) => {
            if (bytes < 1024) {
                return `${bytes} B`;
            }

            return `${(bytes / 1024).toFixed(1)} KB`;
        };

        const renderFiles = () => {
            list.innerHTML = '';
            list.classList.toggle('d-none', selectedFiles.length === 0);

            selectedFiles.forEach((file, index) => {
                const extension = file.name.includes('.') ?
                    file.name.split('.').pop().toLowerCase() :
                    '';
                const isValid = allowedExtensions.includes(extension) && file.size <= maxSize;
                const item = document.createElement('div');
                item.className = `list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 ${isValid ? '' : 'list-group-item-danger'}`;

                const info = document.createElement('div');
                info.className = 'min-width-0';
                info.innerHTML = `<div class="fw-semibold text-break"></div><small class="text-muted"></small>`;
                info.querySelector('div').textContent = file.name;
                info.querySelector('small').textContent = isValid ?
                    formatSize(file.size) :
                    'Format tidak didukung atau ukuran lebih dari 5 MB';

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-sm btn-outline-danger flex-shrink-0';
                removeButton.innerHTML = '<i class="bi bi-x-lg me-1"></i>Hapus';
                removeButton.addEventListener('click', () => {
                    selectedFiles.splice(index, 1);
                    syncInput();
                    renderFiles();
                });

                item.append(info, removeButton);
                list.appendChild(item);
            });
        };

        const syncInput = () => {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach((file) => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        };

        input.addEventListener('change', () => {
            selectedFiles = Array.from(input.files);
            renderFiles();
        });
    })();
</script>

<script>
    (() => {
        const selectAllCheckbox = document.getElementById('selectAllProjectFiles');
        const fileCheckboxes = document.querySelectorAll('.project-file-checkbox');
        const bulkBar = document.getElementById('bulkFileActionsBar');
        const countDisplay = document.getElementById('selectedFilesCount');
        const btnBulkDownload = document.getElementById('btnBulkDownloadFiles');
        const btnConfirmBulkDelete = document.getElementById('btnConfirmBulkDelete');
        const formBulkDownload = document.getElementById('formBulkDownloadProjectFiles');
        const formBulkDelete = document.getElementById('formBulkDeleteProjectFiles');
        const downloadInputsContainer = document.getElementById('bulkDownloadInputsContainer');
        const deleteInputsContainer = document.getElementById('bulkDeleteInputsContainer');
        const bulkDeleteText = document.getElementById('bulkDeleteFilesText');

        if (!fileCheckboxes.length) {
            return;
        }

        const getSelectedFileIds = () => {
            const ids = [];
            document.querySelectorAll('.project-file-checkbox:checked').forEach((cb) => {
                ids.push(cb.value);
            });
            return ids;
        };

        const updateBulkBarState = () => {
            const selectedIds = getSelectedFileIds();
            const totalFiles = fileCheckboxes.length;
            const selectedCount = selectedIds.length;

            if (countDisplay) {
                countDisplay.textContent = String(selectedCount);
            }

            if (bulkDeleteText) {
                bulkDeleteText.textContent = `${selectedCount} file`;
            }

            if (bulkBar) {
                if (selectedCount > 0) {
                    bulkBar.classList.remove('d-none');
                } else {
                    bulkBar.classList.add('d-none');
                }
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = selectedCount > 0 && selectedCount === totalFiles;
                selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalFiles;
            }

            // Toggle highlight class on parent list-group-item
            fileCheckboxes.forEach((cb) => {
                const item = cb.closest('.project-file-item');
                if (item) {
                    item.classList.toggle('is-selected', cb.checked);
                }
            });
        };

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', () => {
                const isChecked = selectAllCheckbox.checked;
                fileCheckboxes.forEach((cb) => {
                    cb.checked = isChecked;
                });
                updateBulkBarState();
            });
        }

        fileCheckboxes.forEach((cb) => {
            cb.addEventListener('change', updateBulkBarState);
        });

        // Handle bulk download
        if (btnBulkDownload && formBulkDownload && downloadInputsContainer) {
            btnBulkDownload.addEventListener('click', () => {
                const selectedIds = getSelectedFileIds();
                if (!selectedIds.length) {
                    return;
                }

                downloadInputsContainer.innerHTML = '';
                selectedIds.forEach((id) => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'file_ids[]';
                    hidden.value = id;
                    downloadInputsContainer.appendChild(hidden);
                });

                formBulkDownload.submit();
            });
        }

        // Handle bulk delete confirm
        if (btnConfirmBulkDelete && formBulkDelete && deleteInputsContainer) {
            btnConfirmBulkDelete.addEventListener('click', () => {
                const selectedIds = getSelectedFileIds();
                if (!selectedIds.length) {
                    return;
                }

                deleteInputsContainer.innerHTML = '';
                selectedIds.forEach((id) => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'file_ids[]';
                    hidden.value = id;
                    deleteInputsContainer.appendChild(hidden);
                });

                // Disable button to prevent double-click
                btnConfirmBulkDelete.disabled = true;
                btnConfirmBulkDelete.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Menghapus...';
                formBulkDelete.submit();
            });
        }

        updateBulkBarState();
    })();
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
    (() => {
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        const statusSelect = document.getElementById('projectStatus');
        const promoteInput = document.getElementById('promoteDate');
        const promoteAsterisk = document.getElementById('promoteRequiredAsterisk');
        const promoteHelpText = document.getElementById('promoteHelpText');

        const fpCommonConfig = {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: false,
            locale: (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.id) ? flatpickr.l10ns.id : 'default',
        };

        let fpStart = null;
        let fpEnd = null;

        if (typeof flatpickr !== 'undefined') {
            if (startDate) {
                fpStart = flatpickr(startDate, {
                    ...fpCommonConfig,
                    onChange: function(selectedDates, dateStr) {
                        if (fpEnd && selectedDates[0]) {
                            fpEnd.set('minDate', selectedDates[0]);
                            if (fpEnd.selectedDates[0] && fpEnd.selectedDates[0] < selectedDates[0]) {
                                fpEnd.setDate(selectedDates[0]);
                            }
                        }
                    }
                });
            }

            if (endDate) {
                fpEnd = flatpickr(endDate, {
                    ...fpCommonConfig,
                    minDate: startDate && startDate.value ? startDate.value : null,
                });
            }

            ['unitTestingDate', 'sitDate', 'uatDate', 'promoteDate'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    flatpickr(el, fpCommonConfig);
                }
            });
        }

        const syncPromoteRequirement = () => {
            if (!statusSelect || !promoteInput) return;
            const selectedText = (statusSelect.options[statusSelect.selectedIndex]?.text || '').toLowerCase();
            const isDep = selectedText.includes('deployment') || selectedText.includes('complete') || selectedText.includes('selesai') || selectedText.includes('done');

            if (isDep) {
                promoteInput.setAttribute('required', 'required');
                if (promoteAsterisk) promoteAsterisk.classList.remove('d-none');
                if (promoteHelpText) promoteHelpText.classList.remove('d-none');
            } else {
                promoteInput.removeAttribute('required');
                if (promoteAsterisk) promoteAsterisk.classList.add('d-none');
                if (promoteHelpText) promoteHelpText.classList.add('d-none');
            }
        };

        if (statusSelect) {
            statusSelect.addEventListener('change', syncPromoteRequirement);
            syncPromoteRequirement();
        }
    })();
</script>

<script src="<?= base_url('assets/vendors/choices.js/choices.min.js') ?>"></script>
<script>
    (() => {
        const select = document.getElementById('assignedTo');

        if (!select || typeof window.Choices !== 'function') {
            return;
        }

        const choicesInstance = new window.Choices(select, {
            removeItemButton: true,
            searchEnabled: true,
            searchChoices: true,
            searchFields: ['label'],
            searchFloor: 1,
            searchResultLimit: 100,
            shouldSort: false,
            itemSelectText: 'Pilih',
            noResultsText: 'PIC tidak ditemukan',
            noChoicesText: 'Tidak ada PIC tersedia',
            searchPlaceholderValue: 'Ketik nama atau jabatan PIC...',
            placeholder: true,
            placeholderValue: 'Cari dan pilih PIC...',
        });

        const wrapper = document.getElementById('assignedToChoices');
        const primaryId = wrapper ? wrapper.dataset.primaryId : null;

        if (choicesInstance && primaryId) {
            select.addEventListener('removeItem', (event) => {
                if (String(event.detail?.value) !== String(primaryId)) {
                    return;
                }

                choicesInstance.setChoiceByValue(String(primaryId));
            });
        }
    })();
</script>