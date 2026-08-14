<?php
/**
 * @var array $project
 */
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-heading d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= base_url('/projects') ?>" class="btn btn-sm btn-outline-secondary mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali ke Project Tracker</a>
        <h3><?= esc($project['name']) ?></h3>
        <span class="badge bg-light-primary text-primary"><?= esc($project['project_code']) ?></span>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUpdateProgress">
        <i class="bi bi-pencil-square me-1"></i> Update Progress & Status
    </button>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="page-content">
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-2">Overall Completion Progress</h5>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="progress w-100" style="height: 18px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $project['progress'] ?>%;"></div>
                        </div>
                        <span class="fs-4 fw-bold text-success"><?= $project['progress'] ?>%</span>
                    </div>
                    <div class="d-flex justify-content-between text-sm text-muted">
                        <span><i class="bi bi-calendar-event me-1"></i> Start Date: <strong><?= date('d M Y', strtotime($project['start_date'])) ?></strong></span>
                        <span><i class="bi bi-calendar-check me-1"></i> Deadline: <strong><?= date('d M Y', strtotime($project['end_date'])) ?></strong></span>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-diagram-3 me-2 text-primary"></i>Tahapan & Timeline Milestone</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 border rounded <?= $project['unit_testing_date'] ? 'bg-light-success border-success' : 'bg-light' ?>">
                                <i class="bi bi-bug fs-3 text-primary d-block mb-1"></i>
                                <span class="fw-bold d-block text-sm">Unit Testing</span>
                                <small class="text-muted"><?= $project['unit_testing_date'] ? date('d M Y', strtotime($project['unit_testing_date'])) : 'Belum di-set' ?></small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 border rounded <?= $project['sit_date'] ? 'bg-light-info border-info' : 'bg-light' ?>">
                                <i class="bi bi-diagram-2 fs-3 text-info d-block mb-1"></i>
                                <span class="fw-bold d-block text-sm">SIT Phase</span>
                                <small class="text-muted"><?= $project['sit_date'] ? date('d M Y', strtotime($project['sit_date'])) : 'Belum di-set' ?></small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 border rounded <?= $project['uat_date'] ? 'bg-light-warning border-warning' : 'bg-light' ?>">
                                <i class="bi bi-shield-check fs-3 text-warning d-block mb-1"></i>
                                <span class="fw-bold d-block text-sm">UAT Phase</span>
                                <small class="text-muted"><?= $project['uat_date'] ? date('d M Y', strtotime($project['uat_date'])) : 'Belum di-set' ?></small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 border rounded <?= $project['promote_date'] ? 'bg-light-primary border-primary' : 'bg-light' ?>">
                                <i class="bi bi-rocket-takeoff fs-3 text-success d-block mb-1"></i>
                                <span class="fw-bold d-block text-sm">Promote (Deploy)</span>
                                <small class="text-muted"><?= $project['promote_date'] ? date('d M Y', strtotime($project['promote_date'])) : 'Belum di-set' ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header pb-2">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Catatan & Dokumentasi</h5>
                </div>
                <div class="card-body pt-2">
                    <p class="mb-0 text-muted"><?= nl2br(esc($project['notes'] ?? 'Tidak ada catatan tambahan untuk proyek ini.')) ?></p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header pb-2">
                    <h5 class="card-title mb-0 fs-6 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Assigned To / PIC</h5>
                </div>
                <div class="card-body pt-2">
                    <?php if (!empty($project['assigned_users'])) : ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($project['assigned_users'] as $assignedUser) : ?>
                                <li class="list-group-item px-0 d-flex align-items-center gap-3">
                                    <div class="avatar bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; border-radius: 50%;">
                                        <?= strtoupper(substr($assignedUser['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= esc($assignedUser['name']) ?></h6>
                                        <small class="text-muted d-block"><?= esc($assignedUser['job_title'] ?? 'Software Engineer') ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p class="text-muted text-sm mb-0 py-2">Belum ada user yang ditugaskan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUpdateProgress" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('/projects/update-progress/' . $project['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil-square me-2"></i>Update Progress Proyek</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Percentage Progress (%)</label>
                        <input type="number" name="progress" class="form-control form-control-lg" min="0" max="100" value="<?= $project['progress'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Project Status</label>
                        <select name="status" class="form-select" required>
                            <?php
                            $statuses = ['Planning', 'In Progress', 'Testing/QA', 'Review', 'Completed', 'On Hold'];
                            foreach ($statuses as $st) : ?>
                                <option value="<?= $st ?>" <?= $project['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan Progress Tambahan</label>
                        <textarea name="notes" class="form-control" rows="3"><?= esc($project['notes']) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
