<?php

/**
 * @var array $statusOptions
 * @var array $projects
 * @var array $user
 * @var string|null $selectedStatus
 */
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-heading d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3>Project Tracker</h3>
        <p class="text-subtitle text-muted mb-0">Kelola dan pantau seluruh proyek departemen secara real-time.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddProject">
        <i class="bi bi-plus-circle me-1"></i> Tambah Proyek
    </button>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="page-content">

    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('/projects') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari Kode atau Nama Proyek..." value="<?= esc($keyword ?? '') ?>">
                </div>
                <div class="col-12 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <?php foreach ($statusOptions as $st) : ?>
                            <option value="<?= $st ?>" <?= ($selectedStatus === $st) ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="<?= base_url('/projects') ?>" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode & Nama Proyek</th>
                            <th>Status</th>
                            <th style="width: 20%;">Progress</th>
                            <th>Developer (PIC)</th>
                            <th>Target Promote</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($projects)) : ?>
                            <?php foreach ($projects as $prj) : ?>
                                <?php
                                $statusBadge = match ($prj['status']) {
                                    'Completed'    => 'bg-success',
                                    'In Progress'  => 'bg-primary',
                                    'Testing/QA'   => 'bg-info',
                                    'Review' => 'bg-warning text-dark',
                                    'On Hold'      => 'bg-danger',
                                    default        => 'bg-secondary'
                                };
                                ?>
                                <tr>
                                    <td>
                                        <strong class="text-dark d-block"><?= esc($prj['name']) ?></strong>
                                        <span class="badge bg-light-secondary text-muted"><?= esc($prj['project_code']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusBadge ?>"><?= esc($prj['status']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress w-100" style="height: 10px;">
                                                <div class="progress-bar <?= $statusBadge ?>" role="progressbar" style="width: <?= $prj['progress'] ?>%"></div>
                                            </div>
                                            <small class="fw-bold"><?= $prj['progress'] ?>%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($prj['developers'])) : ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach ($prj['developers'] as $dev) : ?>
                                                    <span class="badge bg-light-primary text-primary" title="<?= esc($dev['job_title']) ?>">
                                                        <i class="bi bi-person-fill me-1"></i><?= esc($dev['name']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else : ?>
                                            <span class="text-muted text-sm">- Belum Ada PIC -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="fw-bold text-dark">
                                            <?= $prj['promote_date'] ? date('d M Y', strtotime($prj['promote_date'])) : '-' ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('/projects/detail/' . $prj['id']) ?>" class="btn btn-sm btn-outline-primary me-1" title="Detail Progress">
                                            <i class="bi bi-eye-fill"></i> Detail
                                        </a>
                                        <a href="<?= base_url('/projects/delete/' . $prj['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus proyek ini?')" title="Hapus Proyek">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Data proyek tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddProject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('/projects/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"></i>Tambah Proyek Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Project Code <span class="text-danger">*</span></label>
                            <input type="text" name="project_code" class="form-control" placeholder="Contoh: PRJ-2026-01" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Name Project (Max 250 Char) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" maxlength="250" placeholder="Masukkan nama proyek..." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Project Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <?php foreach ($statusOptions as $st) : ?>
                                    <option value="<?= $st ?>"><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Developer PIC (Bisa Pilih Banyak)</label>
                            <select name="developer_ids[]" class="form-select" multiple style="height: 100px;">
                                <?php foreach ($user as $u) : ?>
                                    <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?> (<?= esc($u['job_title'] ?? 'Staff') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted fs-7">*Tahan Ctrl/Cmd untuk memilih lebih dari 1 developer.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date (Deadline) <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold text-sm">Unit Testing</label>
                            <input type="date" name="unit_testing_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-sm">SIT Date</label>
                            <input type="date" name="sit_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-sm">UAT Date</label>
                            <input type="date" name="uat_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-sm">Promote (Deploy)</label>
                            <input type="date" name="promote_date" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Catatan Proyek</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan khusus proyek..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Proyek</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>