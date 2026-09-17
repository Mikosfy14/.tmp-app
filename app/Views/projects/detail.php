<?php

/**
 * @var array $project
 * @var array $projectFiles
 */

helper(['deadline', 'navigation']);
$backNav = get_contextual_back('/projects', 'Kembali ke Project Tracker');

$statusBadge = match ($project['status'] ?? '') {
    'Planning' => 'bg-secondary text-white',
    'Defining' => 'bg-info text-dark',
    'Designing' => 'bg-primary text-white',
    'Building' => 'bg-warning text-dark',
    'Testing' => 'bg-danger text-white',
    'Deployment' => 'bg-success text-white',
    default => 'bg-secondary text-white',
};

$dateValue = static fn($value): string => !empty($value) ? date('d M Y', strtotime($value)) : '-';

$isCompleted = is_project_completed($project);
$deadline = get_deadline_status($project['end_date'] ?? null, $isCompleted);
$deadlineLabel = $deadline['label'];
$deadlineBadge = $deadline['badge_class'];

// Calculate relative deadline or completion timing
$timingText = null;
$timingClass = 'text-muted';

if ($isCompleted) {
    if (!empty($project['promote_date']) && !empty($project['end_date'])) {
        try {
            $endDateObj = new DateTimeImmutable(date('Y-m-d', strtotime($project['end_date'])));
            $promoteDateObj = new DateTimeImmutable(date('Y-m-d', strtotime($project['promote_date'])));
            $delayDays = (int) $endDateObj->diff($promoteDateObj)->format('%r%a');

            if ($delayDays > 0) {
                $timingText = "Telat {$delayDays} hari";
                $timingClass = "text-danger fw-semibold";
            } elseif ($delayDays === 0) {
                $timingText = "Tepat Waktu";
                $timingClass = "text-success fw-semibold";
            } else {
                $earlyDays = abs($delayDays);
                $timingText = "Lebih cepat {$earlyDays} hari";
                $timingClass = "text-success fw-semibold";
            }
        } catch (\Exception $e) {
            $timingText = null;
        }
    }
} else {
    if (!empty($project['end_date'])) {
        try {
            $today = new DateTimeImmutable(date('Y-m-d'));
            $targetDate = new DateTimeImmutable(date('Y-m-d', strtotime($project['end_date'])));
            $diff = (int) $today->diff($targetDate)->format('%r%a');
            if ($diff < 0) {
                $timingText = abs($diff) . ' hari terlambat';
                $timingClass = 'text-danger fw-semibold';
            } elseif ($diff === 0) {
                $timingText = 'Tenggat hari ini';
                $timingClass = 'text-danger fw-bold';
            } else {
                $timingText = 'Sisa ' . $diff . ' hari';
                $timingClass = !empty($deadline['class']) ? 'text-' . esc($deadline['class']) . ' fw-semibold' : 'text-muted';
            }
        } catch (\Exception $e) {
            $timingText = null;
        }
    }
}
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .project-detail-header {
        background-color: var(--bs-card-bg, #ffffff);
        border: 1px solid var(--bs-border-color, #e9ecef);
        border-radius: 12px;
        padding: 1.5rem;
    }

    [data-bs-theme="dark"] .project-detail-header {
        background-color: #1e1e2d !important;
        border-color: #2b2b40 !important;
    }

    [data-bs-theme="dark"] .project-detail-header h3,
    [data-bs-theme="dark"] .project-detail-header .meta-item-value {
        color: #ffffff !important;
    }

    [data-bs-theme="dark"] .project-detail-header .border-top {
        border-color: #2b2b40 !important;
    }

    .project-code-badge {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .meta-item-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    [data-bs-theme="dark"] .meta-item-label {
        color: #a0aec0;
    }

    .meta-item-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--bs-body-color);
    }

    .milestone-track {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    @media (max-width: 767.98px) {
        .project-detail-header {
            padding: 1.25rem 1rem;
        }

        .project-header-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            width: 100%;
        }

        .project-header-actions .btn {
            width: 100%;
            text-align: center;
            justify-content: center;
        }

        .milestone-track {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .milestone-box {
            padding: 0.75rem;
        }

        .milestone-name {
            font-size: 0.72rem;
        }

        .milestone-date {
            font-size: 0.875rem;
        }

        .btn-file-download {
            width: 38px !important;
            height: 38px !important;
        }
    }

    .milestone-box {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color, #e9ecef);
        border-radius: 10px;
        padding: 1rem;
        position: relative;
        transition: border-color 0.2s ease;
    }

    [data-bs-theme="dark"] .milestone-box {
        background-color: #252539 !important;
        border-color: #2b2b40 !important;
    }

    .milestone-box.has-date {
        border-left: 3px solid #435ebe;
    }

    [data-bs-theme="dark"] .milestone-box.has-date {
        border-left-color: #8fa0f0;
    }

    .milestone-name {
        font-size: 0.78rem;
        font-weight: 700;
        color: #495057;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    [data-bs-theme="dark"] .milestone-name {
        color: #cbd5e1;
    }

    .milestone-date {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--bs-body-color);
    }

    [data-bs-theme="dark"] .milestone-date {
        color: #ffffff !important;
    }

    .avatar-initial {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background-color: rgba(67, 94, 190, 0.12);
        color: #435ebe;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    [data-bs-theme="dark"] .avatar-initial {
        background-color: rgba(143, 160, 240, 0.18);
        color: #8fa0f0;
    }

    .pic-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--bs-border-color, #f1f3f5);
    }

    [data-bs-theme="dark"] .pic-item {
        border-bottom-color: #2b2b40 !important;
    }

    .pic-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .pic-item:first-child {
        padding-top: 0;
    }

    .file-item-row {
        padding: 0.85rem 0;
        border-bottom: 1px solid var(--bs-border-color, #f1f3f5);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        min-width: 0;
        width: 100%;
    }

    [data-bs-theme="dark"] .file-item-row {
        border-bottom-color: #2b2b40 !important;
    }

    .file-item-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .file-item-row:first-child {
        padding-top: 0;
    }

    .file-icon-box {
        width: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .file-info-col {
        min-width: 0;
        flex: 1 1 auto;
        overflow: hidden;
    }

    .file-name-link {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        color: var(--bs-body-color);
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
    }

    [data-bs-theme="dark"] .file-name-link {
        color: #e6eaee !important;
    }

    .file-name-link:hover {
        color: #435ebe;
        text-decoration: underline;
    }

    [data-bs-theme="dark"] .file-name-link:hover {
        color: #8fa0f0 !important;
    }

    .file-meta-text {
        font-size: 0.75rem;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        margin-top: 0.15rem;
    }

    [data-bs-theme="dark"] .file-meta-text {
        color: #a0aec0;
    }

    .btn-file-download {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    [data-bs-theme="dark"] .btn-file-download {
        background-color: #252539 !important;
        border-color: #2b2b40 !important;
        color: #a0aec0 !important;
    }

    [data-bs-theme="dark"] .btn-file-download:hover {
        background-color: #2e2e46 !important;
        color: #ffffff !important;
    }

    .notes-container {
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color, #e9ecef);
        border-radius: 10px;
        padding: 1.25rem;
        line-height: 1.7;
        font-size: 0.95rem;
    }

    [data-bs-theme="dark"] .notes-container {
        background-color: #252539 !important;
        border-color: #2b2b40 !important;
        color: #e6eaee !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Navigation Back Link -->
<div class="mb-3">
    <a href="<?= esc($backNav['url'], 'attr') ?>" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> <?= esc($backNav['label']) ?>
    </a>
</div>

<!-- Feedback Flash Alert -->
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Executive Header Card (Zero Redundancy) -->
<div class="card project-detail-header shadow-sm mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                <span class="badge bg-light-primary text-primary border border-primary-subtle project-code-badge">
                    <?= esc($project['project_code']) ?>
                </span>
                <span class="badge <?= $statusBadge ?> fw-semibold">
                    <?= esc($project['status'] ?? '-') ?>
                </span>
                <?php if (!empty($project['database_type_name'])) : ?>
                    <span class="badge bg-light-info text-info border border-info-subtle fw-semibold">
                        <?= esc($project['database_type_name']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <h3 class="h4 fw-bold mb-0 text-body">
                <?= esc($project['name']) ?>
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2 align-self-stretch align-self-md-auto project-header-actions">
            <a href="<?= base_url('/projects/edit/' . $project['id']) ?>" class="btn btn-sm btn-outline-primary px-3 py-2 fw-semibold">
                Edit Project
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalDeleteProject">
                Hapus
            </button>
        </div>
    </div>

    <!-- Timeline & Deadline Integrated Row (No Promote Date here to avoid milestone redundancy) -->
    <div class="row g-3 pt-4 mt-3 border-top">
        <div class="col-6 col-md-4">
            <div class="meta-item-label">Tanggal Mulai</div>
            <div class="meta-item-value"><?= $dateValue($project['start_date'] ?? null) ?></div>
        </div>
        <div class="col-6 col-md-4">
            <div class="meta-item-label">Tanggal Selesai</div>
            <div class="meta-item-value"><?= $dateValue($project['end_date'] ?? null) ?></div>
        </div>
        <div class="col-12 col-md-4">
            <div class="meta-item-label">Status Tenggat</div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge <?= $deadlineBadge ?> fw-semibold">
                    <?= esc($deadlineLabel) ?>
                </span>
                <?php if ($timingText !== null) : ?>
                    <span class="<?= $timingClass ?>" style="font-size: 0.9rem;">
                        <?= esc($timingText) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="row g-4">
        <!-- Main Content Area -->
        <div class="col-12 col-lg-8">
            <!-- Milestone Track (Unit Testing -> SIT -> UAT -> Promote) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Target Milestone</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="milestone-track">
                        <div class="milestone-box <?= !empty($project['unit_testing_date']) ? 'has-date' : '' ?>">
                            <div class="milestone-name">Unit Testing</div>
                            <div class="milestone-date"><?= $dateValue($project['unit_testing_date'] ?? null) ?></div>
                        </div>
                        <div class="milestone-box <?= !empty($project['sit_date']) ? 'has-date' : '' ?>">
                            <div class="milestone-name">SIT</div>
                            <div class="milestone-date"><?= $dateValue($project['sit_date'] ?? null) ?></div>
                        </div>
                        <div class="milestone-box <?= !empty($project['uat_date']) ? 'has-date' : '' ?>">
                            <div class="milestone-name">UAT</div>
                            <div class="milestone-date"><?= $dateValue($project['uat_date'] ?? null) ?></div>
                        </div>
                        <div class="milestone-box <?= !empty($project['promote_date']) ? 'has-date' : '' ?>">
                            <div class="milestone-name">Promote</div>
                            <div class="milestone-date"><?= $dateValue($project['promote_date'] ?? null) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="card shadow-sm">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Catatan Proyek</h5>
                </div>
                <div class="card-body pt-3">
                    <?php if (!empty(trim((string) ($project['notes'] ?? '')))) : ?>
                        <div class="notes-container text-body">
                            <?= nl2br(esc($project['notes'])) ?>
                        </div>
                    <?php else : ?>
                        <div class="text-muted small py-2">
                            Tidak ada catatan tambahan untuk project ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-12 col-lg-4">
            <!-- Assigned Team (PIC) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Tim Penanggung Jawab</h5>
                </div>
                <div class="card-body pt-3">
                    <?php if (!empty($project['assigned_users'])) : ?>
                        <?php
                        $primaryPicId = (int) (explode(',', (string) ($project['assigned_to'] ?? ''))[0] ?? 0);
                        ?>
                        <div class="d-flex flex-column">
                            <?php foreach ($project['assigned_users'] as $assignedUser) : ?>
                                <?php
                                $isPrimaryPic = ((int) ($assignedUser['user_id'] ?? $assignedUser['id'] ?? 0) === $primaryPicId);
                                ?>
                                <div class="pic-item d-flex align-items-center gap-3">
                                    <div class="avatar-initial">
                                        <?= esc(strtoupper(substr($assignedUser['name'] ?? 'U', 0, 1))) ?>
                                    </div>
                                    <div class="min-width-0 flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fw-bold text-body small mb-0"><?= esc($assignedUser['name']) ?></span>
                                            <?php if ($isPrimaryPic) : ?>
                                                <span class="badge bg-light-success text-success border border-success-subtle py-1" style="font-size: 0.65rem;">
                                                    Penanggung Jawab
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.78rem;">
                                            <?= esc($assignedUser['job_title'] ?? '-') ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="text-muted small py-2">Belum ada anggota tim yang ditugaskan.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Attached Files -->
            <div class="card shadow-sm">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Berkas Proyek</h5>
                </div>
                <div class="card-body pt-3">
                    <?php if (!empty($projectFiles)) : ?>
                        <div class="d-flex flex-column">
                            <?php foreach ($projectFiles as $file) : ?>
                                <?php
                                $ext = strtolower(pathinfo($file['original_name'] ?? '', PATHINFO_EXTENSION));
                                $fileIconClass = match ($ext) {
                                    'pdf' => 'fas fa-file-pdf text-danger fs-5',
                                    'xls', 'xlsx' => 'fas fa-file-excel text-success fs-5',
                                    'doc', 'docx' => 'fas fa-file-word text-primary fs-5',
                                    default => 'fas fa-file-lines text-secondary fs-5',
                                };
                                $fileSizeKb = number_format(((int) ($file['file_size'] ?? 0)) / 1024, 1) . ' KB';
                                $fileDate = !empty($file['created_at']) ? date('d M Y', strtotime($file['created_at'])) : '';
                                ?>
                                <div class="file-item-row">
                                    <div class="d-flex align-items-center gap-2" style="min-width: 0; flex: 1 1 auto; overflow: hidden;">
                                        <div class="file-icon-box">
                                            <i class="<?= $fileIconClass ?>"></i>
                                        </div>
                                        <div class="file-info-col">
                                            <a href="<?= base_url('/projects/files/' . $file['id'] . '/download') ?>" class="file-name-link" title="<?= esc($file['original_name']) ?>">
                                                <?= esc($file['original_name']) ?>
                                            </a>
                                            <small class="file-meta-text">
                                                <?= $fileSizeKb ?>
                                                <?php if ($fileDate) : ?>
                                                    &middot; <?= $fileDate ?>
                                                <?php endif; ?>
                                                <?php if (!empty($file['uploaded_by_name'])) : ?>
                                                    &middot; <?= esc($file['uploaded_by_name']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <a href="<?= base_url('/projects/files/' . $file['id'] . '/download') ?>" class="btn btn-sm btn-light btn-file-download text-muted" title="Unduh berkas">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="text-muted small py-2">Belum ada berkas terunggah pada project ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation (Structure and IDs Preserved) -->
<div class="modal fade" id="modalDeleteProject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-2">Hapus Proyek</h6>
                <p class="text-muted mb-0">
                    Proyek <strong><?= esc($project['name']) ?></strong> akan dihapus permanen dari sistem. Lanjutkan hanya jika data ini sudah tidak dibutuhkan.
                </p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/projects/delete/' . $project['id']) ?>" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" data-cooldown="3">Ya, Hapus Project</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>