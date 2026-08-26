<?php

/**
 * @var array $statusOptions
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
$selectedAssignedIds = array_values(array_filter(array_map('intval', $selectedAssignedIds ?? [])));
$selectedAssignedIds = !empty($selectedAssignedIds) ? $selectedAssignedIds : [(int) session()->get('user_id')];
$responsibleAssignedId = $project
    ? (int) (explode(',', (string) ($project['assigned_to'] ?? ''))[0] ?? 0)
    : (int) session()->get('user_id');
$responsibleAssignedId = $responsibleAssignedId > 0 ? $responsibleAssignedId : (int) session()->get('user_id');
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

    .project-form-page-header .btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
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

    #assignedToChoices .choices {
        margin-bottom: 0;
    }

    #assignedToChoices .choices__inner {
        min-height: 46px;
        padding: 6px 8px 2px;
        background-color: #fff;
        border-color: #dfe3eb;
        border-radius: 6px;
    }

    #assignedToChoices .choices__input {
        min-width: 180px;
        margin-bottom: 4px;
        background-color: transparent;
    }

    #selectedProjectFiles,
    .project-existing-files {
        max-height: 240px;
        overflow-y: auto;
        padding-right: .25rem;
    }

    #selectedProjectFiles:empty,
    .project-existing-files:empty {
        display: none;
    }

    #assignedToChoices .choices__list--multiple .choices__item {
        background-color: #435ebe;
        border-color: #364da3;
        color: #fff;
    }

    #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"] {
        background-color: #4fbe87;
        border-color: #3d996d;
        font-weight: 700;
    }

    #assignedToChoices .choices__list--multiple .choices__item.is-highlighted,
    #assignedToChoices .choices__list--multiple .choices__item:hover {
        background-color: #364da3;
        border-color: #2f428e;
    }

    #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"]:hover,
    #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"].is-highlighted {
        background-color: #3d996d;
        border-color: #307a57;
    }

    #assignedToChoices .choices__list--multiple .choices__button {
        border-left-color: rgba(255, 255, 255, .45);
    }

    #assignedToChoices .choices__list--multiple .choices__item[data-value="<?= (int) $responsibleAssignedId ?>"] .choices__button {
        display: none;
    }

    [data-bs-theme="dark"] #assignedToChoices .choices__inner {
        background-color: #151521;
        border-color: #2b2b40;
        color: #f5f7ff;
    }

    [data-bs-theme="dark"] #assignedToChoices .choices__input,
    [data-bs-theme="dark"] #assignedToChoices .choices__list--dropdown {
        background-color: #151521;
        border-color: #2b2b40;
        color: #f5f7ff;
    }

    [data-bs-theme="dark"] #assignedToChoices .choices__item--choice.is-highlighted {
        background-color: #252539;
    }

    [data-bs-theme="dark"] .project-form-card,
    [data-bs-theme="dark"] .project-form-actions,
    [data-bs-theme="dark"] .project-form-section {
        border-color: #2b2b40;
    }

    [data-bs-theme="dark"] .project-form-actions,
    [data-bs-theme="dark"] .project-form-upload {
        background: #252539;
    }

    [data-bs-theme="dark"] .project-form-card .form-label {
        color: #f5f7ff;
    }

    @media (max-width: 575.98px) {

        .project-form-section,
        .project-form-actions {
            padding: 1rem;
        }

        .project-form-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .project-form-actions .d-flex {
            flex-direction: column-reverse;
        }

        .project-form-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="card project-form-card mb-4">
    <form action="<?= esc($formAction) ?>" method="POST" enctype="multipart/form-data">
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
                <div class="col-md-4">
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
                <div class="col-md-8">
                    <label for="assignedTo" class="form-label">Assigned To / PIC</label>
                    <div id="assignedToChoices" data-primary-id="<?= (int) $responsibleAssignedId ?>">
                        <select id="assignedTo" name="assigned_to[]" class="form-select" multiple>
                            <?php foreach ($users as $user) : ?>
                                <?php $isPrimary = (int) $user['id'] === $responsibleAssignedId; ?>
                                <option value="<?= esc($user['id']) ?>" <?= $isSelected((int) $user['id']) ? 'selected' : '' ?>>
                                    <?= esc($user['name']) ?><?= !empty($user['job_title']) ? ' - ' . esc($user['job_title']) : '' ?><?= (int) ($user['is_active'] ?? 0) === 0 ? ' (Nonaktif)' : '' ?><?= $isPrimary ? ' (Penanggung Jawab)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($primaryAssignedUser) : ?>
                        <div class="form-text">
                            <i class="bi bi-person-check-fill me-1 text-success" aria-hidden="true"></i>
                            <strong><?= esc($primaryAssignedUser['name']) ?></strong> adalah PIC penanggung jawab.
                            <?= $project ? 'PIC utama dipertahankan saat project diedit.' : 'Pembuat project otomatis menjadi PIC utama.' ?>
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
                    <input type="date" id="startDate" name="start_date" class="form-control" value="<?= esc($value('start_date', $project['start_date'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="endDate" class="form-label">End Date <span class="text-danger">*</span></label>
                    <input type="date" id="endDate" name="end_date" class="form-control" value="<?= esc($value('end_date', $project['end_date'] ?? '')) ?>" required>
                </div>
                <div class="col-12 mt-4">
                    <div class="small fw-bold text-muted text-uppercase">Target Milestone</div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="unitTestingDate" class="form-label">Unit Testing</label>
                    <input type="date" id="unitTestingDate" name="unit_testing_date" class="form-control" value="<?= esc($value('unit_testing_date', $project['unit_testing_date'] ?? '')) ?>">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="sitDate" class="form-label">SIT</label>
                    <input type="date" id="sitDate" name="sit_date" class="form-control" value="<?= esc($value('sit_date', $project['sit_date'] ?? '')) ?>">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="uatDate" class="form-label">UAT</label>
                    <input type="date" id="uatDate" name="uat_date" class="form-control" value="<?= esc($value('uat_date', $project['uat_date'] ?? '')) ?>">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="promoteDate" class="form-label">Promote</label>
                    <input type="date" id="promoteDate" name="promote_date" class="form-control" value="<?= esc($value('promote_date', $project['promote_date'] ?? '')) ?>">
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
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h6 class="fw-bold mb-0">File Tersimpan</h6>
                        <span class="badge bg-light-primary text-primary"><?= count($projectFiles) ?> file</span>
                    </div>
                    <div class="list-group project-existing-files">
                        <?php foreach ($projectFiles as $file) : ?>
                            <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div class="d-flex align-items-center gap-3 min-width-0">
                                    <span class="project-form-section-icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span>
                                    <div class="min-width-0">
                                        <div class="fw-semibold text-break"><?= esc($file['original_name']) ?></div>
                                        <small class="text-muted">
                                            <?= esc(number_format(((int) ($file['file_size'] ?? 0)) / 1024, 1)) ?> KB
                                            <?php if (!empty($file['uploaded_by_name'])) : ?>
                                                &middot; <?= esc($file['uploaded_by_name']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                                    <a href="<?= base_url('/projects/files/' . (int) $file['id'] . '/download') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download" aria-hidden="true"></i> Download
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteProjectFile<?= (int) $file['id'] ?>">
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
                    <i class="bi bi-check-circle" aria-hidden="true"></i><?= esc($submitLabel) ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php if (!empty($projectFiles)) : ?>
    <?php foreach ($projectFiles as $file) : ?>
        <div class="modal fade" id="modalDeleteProjectFile<?= (int) $file['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus File Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
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
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');

        if (!startDate || !endDate) {
            return;
        }

        const syncEndDateLimit = () => {
            endDate.min = startDate.value;

            if (startDate.value && endDate.value && endDate.value < startDate.value) {
                endDate.value = startDate.value;
            }
        };

        startDate.addEventListener('change', syncEndDateLimit);
        syncEndDateLimit();
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
            shouldSort: false,
            itemSelectText: 'Pilih',
            noResultsText: 'PIC tidak ditemukan',
            noChoicesText: 'Tidak ada PIC tersedia',
            searchPlaceholderValue: 'Ketik nama PIC untuk mencari...',
            placeholder: true,
            placeholderValue: 'Pilih satu atau beberapa PIC',
        });

        const primaryId = document.getElementById('assignedToChoices').dataset.primaryId;

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
