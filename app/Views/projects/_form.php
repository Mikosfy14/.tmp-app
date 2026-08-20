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
?>

<div class="card shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="<?= esc($formAction) ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-bold">Project Code <span class="text-danger">*</span></label>
                    <input type="text" name="project_code" class="form-control" maxlength="50" value="<?= esc($value('project_code', $project['project_code'] ?? '')) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Nama Project <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" maxlength="250" value="<?= esc($value('name', $project['name'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Status SDLC <span class="text-danger">*</span></label>
                    <select name="project_status_id" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
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
                    <label class="form-label fw-bold">Assigned To / PIC</label>
                    <select name="assigned_to[]" class="form-select" multiple size="6">
                        <?php foreach ($users as $user) : ?>
                            <option value="<?= esc($user['id']) ?>" <?= $isSelected((int) $user['id']) ? 'selected' : '' ?>>
                                <?= esc($user['name']) ?><?= !empty($user['job_title']) ? ' - ' . esc($user['job_title']) : '' ?><?= (int) ($user['is_active'] ?? 0) === 0 ? ' (Nonaktif)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">User login tetap menjadi penanggung jawab utama saat project dibuat.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" value="<?= esc($value('start_date', $project['start_date'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control" value="<?= esc($value('end_date', $project['end_date'] ?? '')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Unit Testing</label>
                    <input type="date" name="unit_testing_date" class="form-control" value="<?= esc($value('unit_testing_date', $project['unit_testing_date'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">SIT</label>
                    <input type="date" name="sit_date" class="form-control" value="<?= esc($value('sit_date', $project['sit_date'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">UAT</label>
                    <input type="date" name="uat_date" class="form-control" value="<?= esc($value('uat_date', $project['uat_date'] ?? '')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Promote</label>
                    <input type="date" name="promote_date" class="form-control" value="<?= esc($value('promote_date', $project['promote_date'] ?? '')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Notes</label>
                    <textarea name="notes" class="form-control" rows="4"><?= esc($value('notes', $project['notes'] ?? '')) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">File Project</label>
                    <input type="file" id="projectFilesInput" name="project_files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.xls,.xlsx">
                    <small class="text-muted">Pilih satu atau beberapa file sekaligus. Format: PDF, Word, Excel. Maksimal 5 MB per file.</small>
                    <div id="selectedProjectFiles" class="list-group mt-3 d-none" aria-live="polite"></div>
                </div>
                <?php if (!empty($project) && !empty($projectFiles)) : ?>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <h6 class="fw-bold mb-3">File yang Sudah Diupload</h6>
                            <div class="list-group">
                                <?php foreach ($projectFiles as $file) : ?>
                                    <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                        <div class="min-width-0">
                                            <div class="fw-semibold text-break"><?= esc($file['original_name']) ?></div>
                                            <small class="text-muted">
                                                <?= esc(number_format(((int) ($file['file_size'] ?? 0)) / 1024, 1)) ?> KB
                                                <?php if (!empty($file['uploaded_by_name'])) : ?>
                                                    &middot; <?= esc($file['uploaded_by_name']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                                            <a href="<?= base_url('/projects/files/' . (int) $file['id'] . '/download') ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download me-1"></i> Download
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteProjectFile<?= (int) $file['id'] ?>">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php elseif (!empty($project)) : ?>
                    <div class="col-12">
                        <div class="alert alert-light border mb-0">Belum ada file yang diupload untuk project ini.</div>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="<?= base_url('/projects') ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><?= esc($submitLabel) ?></button>
                </div>
            </div>
        </form>
    </div>
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
                const extension = file.name.includes('.')
                    ? file.name.split('.').pop().toLowerCase()
                    : '';
                const isValid = allowedExtensions.includes(extension) && file.size <= maxSize;
                const item = document.createElement('div');
                item.className = `list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 ${isValid ? '' : 'list-group-item-danger'}`;

                const info = document.createElement('div');
                info.className = 'min-width-0';
                info.innerHTML = `<div class="fw-semibold text-break"></div><small class="text-muted"></small>`;
                info.querySelector('div').textContent = file.name;
                info.querySelector('small').textContent = isValid
                    ? formatSize(file.size)
                    : 'Format tidak didukung atau ukuran lebih dari 5 MB';

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-sm btn-outline-danger flex-shrink-0';
                removeButton.textContent = 'Hapus dari daftar';
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
