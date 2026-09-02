<?php
/**
 * @var array $project
 * @var array $projectFiles
 */

helper('deadline');

$statusBadge = match ($project['status'] ?? '') {
    'Planning' => 'bg-secondary',
    'Defining' => 'bg-info',
    'Designing' => 'bg-primary',
    'Building' => 'bg-warning text-dark',
    'Testing' => 'bg-danger',
    'Deployment' => 'bg-success',
    default => 'bg-secondary',
};

$dateValue = static fn ($value): string => !empty($value) ? date('d M Y', strtotime($value)) : '-';

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
                $timingText = "Selesai Tepat Waktu";
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

<?= $this->section('content') ?>

<div class="page-heading d-flex justify-content-between align-items-start mb-3">
    <div>
        <a href="<?= base_url('/projects') ?>" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Project Tracker
        </a>
        <h3><?= esc($project['name']) ?></h3>
        <span class="badge bg-light-primary text-primary"><?= esc($project['project_code']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('/projects/edit/' . $project['id']) ?>" class="btn btn-warning">
            <i class="bi bi-pencil-square me-1"></i> Edit Project
        </a>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteProject">
            <i class="bi bi-trash-fill me-1"></i> Hapus Project
        </button>
    </div>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="page-content">
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Ringkasan Project</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Status SDLC</small>
                            <span class="badge <?= $statusBadge ?>"><?= esc($project['status'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Start Date</small>
                            <strong><?= $dateValue($project['start_date'] ?? null) ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">End Date</small>
                            <strong><?= $dateValue($project['end_date'] ?? null) ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Deadline Status</small>
                            <span class="badge <?= $deadlineBadge ?>"><?= esc($deadlineLabel) ?></span>
                        </div>
                        <?php if ($timingText !== null) : ?>
                            <div class="col-md-8">
                                <small class="text-muted d-block"><?= $isCompleted ? 'Keterangan Waktu Rilis' : 'Sisa Waktu Pengerjaan' ?></small>
                                <span class="<?= $timingClass ?>" style="font-size: 0.9rem;">
                                    <i class="bi <?= $isCompleted ? ($timingClass === 'text-danger fw-semibold' ? 'bi-exclamation-circle-fill text-danger' : 'bi-check-circle-fill text-success') : 'bi-clock-history' ?> me-1"></i><?= esc($timingText) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-diagram-3 me-2 text-primary"></i>Timeline Project</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 border rounded h-100">
                                <small class="text-muted d-block">Unit Testing</small>
                                <strong><?= $dateValue($project['unit_testing_date'] ?? null) ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 border rounded h-100">
                                <small class="text-muted d-block">SIT</small>
                                <strong><?= $dateValue($project['sit_date'] ?? null) ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 border rounded h-100">
                                <small class="text-muted d-block">UAT</small>
                                <strong><?= $dateValue($project['uat_date'] ?? null) ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 border rounded h-100">
                                <small class="text-muted d-block">Promote</small>
                                <strong><?= $dateValue($project['promote_date'] ?? null) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted"><?= nl2br(esc($project['notes'] ?? 'Tidak ada catatan tambahan untuk project ini.')) ?></p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Assigned To / PIC</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($project['assigned_users'])) : ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($project['assigned_users'] as $assignedUser) : ?>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; border-radius: 50%;">
                                        <?= esc(strtoupper(substr($assignedUser['name'] ?? 'U', 0, 1))) ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= esc($assignedUser['name']) ?></h6>
                                        <small class="text-muted"><?= esc($assignedUser['job_title'] ?? '-') ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="text-muted mb-0">Belum ada user yang ditugaskan.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-paperclip me-2 text-primary"></i>File Project</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($projectFiles)) : ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($projectFiles as $file) : ?>
                                <a href="<?= base_url('/projects/files/' . $file['id'] . '/download') ?>" class="list-group-item list-group-item-action px-0 d-flex align-items-start gap-3">
                                    <span class="flex-grow-1 min-width-0">
                                        <strong class="d-block text-dark text-break"><?= esc($file['original_name']) ?></strong>
                                        <small class="text-muted d-block text-break">
                                            <?= number_format(((int) ($file['file_size'] ?? 0)) / 1024, 1) ?> KB
                                            <?php if (!empty($file['uploaded_by_name'])) : ?>
                                                oleh <?= esc($file['uploaded_by_name']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="text-muted mb-0">Belum ada file project yang ditampilkan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDeleteProject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Project</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    Project <strong><?= esc($project['name']) ?></strong> akan dihapus permanen dari daftar project.
                    Lanjutkan hanya jika data ini sudah tidak dibutuhkan.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="<?= base_url('/projects/delete/' . $project['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Ya, Hapus Project</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
