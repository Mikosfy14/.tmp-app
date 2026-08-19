<?php

/**
 * @var array $statusOptions
 * @var array $projects
 * @var array $users
 * @var string|null $selectedStatus
 * @var bool $isFilteredUser
 * @var array|null $targetUser
 */

$previewUsers = !empty($users) ? $users : [
    ['id' => session()->get('user_id') ?: 2, 'name' => session()->get('name') ?: 'User Login', 'job_title' => session()->get('role_name') ?: 'Staff'],
    ['id' => 3, 'name' => 'Diego Pratama', 'job_title' => 'Backend Developer'],
    ['id' => 4, 'name' => 'Indah Permata', 'job_title' => 'Business Analyst'],
];

$previewProjects = [
    [
        'id' => 'preview-001',
        'project_code' => 'PRJ-2026-014',
        'name' => 'Modernisasi Dashboard Monitoring Project',
        'status' => 'Building',
        'notes' => 'Penyusunan dashboard personal, project tracker, filter assigned_to, dan baseline evaluasi kinerja.',
        'start_date' => '2026-08-03',
        'end_date' => '2026-08-28',
        'unit_testing_date' => '2026-08-17',
        'sit_date' => '2026-08-21',
        'uat_date' => '2026-08-25',
        'promote_date' => '2026-08-31',
        'assigned_users' => [$previewUsers[0], $previewUsers[1] ?? $previewUsers[0]],
        'is_preview' => true,
    ],
    [
        'id' => 'preview-002',
        'project_code' => 'PRJ-2026-015',
        'name' => 'Integrasi Auth Lokal Berbasis Role',
        'status' => 'Testing',
        'notes' => 'Validasi username dan password, session login, dan tampilan berdasarkan role.',
        'start_date' => '2026-07-27',
        'end_date' => '2026-08-15',
        'unit_testing_date' => '2026-08-08',
        'sit_date' => '2026-08-12',
        'uat_date' => '2026-08-14',
        'promote_date' => '2026-08-18',
        'assigned_users' => [$previewUsers[0], $previewUsers[2] ?? $previewUsers[0]],
        'is_preview' => true,
    ],
    [
        'id' => 'preview-003',
        'project_code' => 'PRJ-2026-016',
        'name' => 'Katalog Aplikasi Pengelolaan Tim',
        'status' => 'Deployment',
        'notes' => 'Pembuatan layout aplikasi pengelolaan berdasarkan kolom DDL applications.',
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-20',
        'unit_testing_date' => '2026-08-13',
        'sit_date' => '2026-08-15',
        'uat_date' => '2026-08-18',
        'promote_date' => '2026-08-21',
        'assigned_users' => [$previewUsers[0]],
        'is_preview' => true,
    ],
    [
        'id' => 'preview-004',
        'project_code' => 'PRJ-2026-017',
        'name' => 'Penyusunan ERD dan DDL SQL Server',
        'status' => 'Deployment',
        'notes' => 'Finalisasi struktur 4 tabel utama: roles, users, projects, dan applications.',
        'start_date' => '2026-08-03',
        'end_date' => '2026-08-07',
        'unit_testing_date' => '2026-08-05',
        'sit_date' => '2026-08-06',
        'uat_date' => '2026-08-07',
        'promote_date' => '2026-08-07',
        'assigned_users' => [$previewUsers[1] ?? $previewUsers[0], $previewUsers[2] ?? $previewUsers[0]],
        'is_preview' => true,
    ],
    [
        'id' => 'preview-005',
        'project_code' => 'PRJ-2026-018',
        'name' => 'Dashboard Evaluasi Kinerja Individual',
        'status' => 'Planning',
        'notes' => 'Perancangan chart personal, metrik project selesai, dan kesimpulan penilaian kinerja.',
        'start_date' => '2026-08-19',
        'end_date' => '2026-09-04',
        'unit_testing_date' => null,
        'sit_date' => null,
        'uat_date' => null,
        'promote_date' => null,
        'assigned_users' => [$previewUsers[0], $previewUsers[1] ?? $previewUsers[0], $previewUsers[2] ?? $previewUsers[0]],
        'is_preview' => true,
    ],
];

if (!empty($selectedStatus)) {
    $selectedStatusName = '';
    foreach ($statusOptions as $statusOption) {
        if ((string) ($statusOption['id'] ?? '') === (string) $selectedStatus) {
            $selectedStatusName = $statusOption['status_name'] ?? '';
            break;
        }
    }

    $previewProjects = array_filter($previewProjects, static fn ($project) => $project['status'] === $selectedStatusName);
}

if (!empty($keyword)) {
    $searchKeyword = strtolower($keyword);
    $previewProjects = array_filter($previewProjects, static function ($project) use ($searchKeyword) {
        return str_contains(strtolower($project['name']), $searchKeyword)
            || str_contains(strtolower($project['project_code']), $searchKeyword);
    });
}

$isPreviewMode = empty($projects);
$displayProjects = $isPreviewMode ? array_values($previewProjects) : $projects;
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .project-actions {
        min-width: 13.5rem;
        white-space: nowrap;
    }

    .project-action-group {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .375rem;
        flex-wrap: nowrap;
    }

    .project-action-group .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .25rem;
        min-width: 4.25rem;
    }

    @media (max-width: 575.98px) {
        .project-actions {
            min-width: 11.5rem;
        }

        .project-action-group {
            gap: .25rem;
        }

        .project-action-group .btn {
            min-width: auto;
            padding-right: .5rem;
            padding-left: .5rem;
        }
    }
</style>

<div class="page-heading d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3>Project Tracker</h3>
        <p class="text-subtitle text-muted mb-0">
            <?php if (!empty($isFilteredUser) && !empty($targetUser)) : ?>
                Menampilkan project milik <?= esc($targetUser['name']) ?>.
            <?php elseif (session()->get('role_name') === 'Kepala Departemen') : ?>
                Kelola dan pantau seluruh proyek departemen secara real-time.
            <?php else : ?>
                Kelola dan pantau project yang ditugaskan kepada Anda.
            <?php endif; ?>
        </p>
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
            <form action="<?= !empty($isFilteredUser) && !empty($targetUser) ? base_url('/projects/user/' . $targetUser['id']) : base_url('/projects') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari Kode atau Nama Proyek..." value="<?= esc($keyword ?? '') ?>">
                </div>
                <div class="col-12 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <?php foreach ($statusOptions as $st) : ?>
                            <option value="<?= esc($st['id']) ?>" <?= ((string) $selectedStatus === (string) $st['id']) ? 'selected' : '' ?>><?= esc($st['status_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="<?= !empty($isFilteredUser) && !empty($targetUser) ? base_url('/projects/user/' . $targetUser['id']) : base_url('/projects') ?>" class="btn btn-outline-secondary w-100">Reset</a>
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
                            <th>Assigned To (PIC)</th>
                            <th>Timeline</th>
                            <th class="text-center project-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($displayProjects)) : ?>
                            <?php foreach ($displayProjects as $prj) : ?>
                                <?php
                                $statusBadge = match ($prj['status']) {
                                    'Planning'   => 'bg-secondary',
                                    'Defining'   => 'bg-info',
                                    'Designing'  => 'bg-primary',
                                    'Building'   => 'bg-warning text-dark',
                                    'Testing'    => 'bg-danger',
                                    'Deployment' => 'bg-success',
                                    default        => 'bg-secondary'
                                };
                                $isPreview = !empty($prj['is_preview']);
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
                                        <?php if (!empty($prj['assigned_users'])) : ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach ($prj['assigned_users'] as $assignedUser) : ?>
                                                    <span class="badge bg-light-primary text-primary" title="<?= esc($assignedUser['job_title']) ?>">
                                                        <i class="bi bi-person-fill me-1"></i><?= esc($assignedUser['name']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else : ?>
                                            <span class="text-muted text-sm">- Belum Ada User -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="d-block text-muted">Start: <span class="fw-bold text-dark"><?= !empty($prj['start_date']) ? date('d M Y', strtotime($prj['start_date'])) : '-' ?></span></small>
                                        <small class="d-block text-muted">End: <span class="fw-bold text-dark"><?= !empty($prj['end_date']) ? date('d M Y', strtotime($prj['end_date'])) : '-' ?></span></small>
                                        <small class="d-block text-muted">Promote: <span class="fw-bold text-dark"><?= !empty($prj['promote_date']) ? date('d M Y', strtotime($prj['promote_date'])) : '-' ?></span></small>
                                    </td>
                                    <td class="text-center project-actions">
                                        <div class="project-action-group">
                                        <?php if ($isPreview) : ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalDetailProject<?= esc($prj['id']) ?>" title="Detail Project">
                                                <i class="bi bi-eye-fill"></i> Detail
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditProject<?= esc($prj['id']) ?>" title="Edit Project">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus Proyek">
                                                <i class="bi bi-trash-fill"></i> Hapus
                                            </button>
                                        <?php else : ?>
                                            <a href="<?= base_url('/projects/detail/' . $prj['id']) ?>" class="btn btn-sm btn-outline-primary" title="Detail Project">
                                                <i class="bi bi-eye-fill"></i> Detail
                                            </a>
                                            <a href="<?= base_url('/projects/detail/' . $prj['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit Project">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <a href="<?= base_url('/projects/delete/' . $prj['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus proyek ini?')" title="Hapus Proyek">
                                                <i class="bi bi-trash-fill"></i> Hapus
                                            </a>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Data proyek tidak ditemukan.</td>
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
            <form action="<?= $isPreviewMode ? '#' : base_url('/projects/store') ?>" method="POST" <?= $isPreviewMode ? 'onsubmit="return false;"' : '' ?>>
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
                            <select name="project_status_id" class="form-select" required>
                                <?php foreach ($statusOptions as $st) : ?>
                                    <option value="<?= esc($st['id']) ?>"><?= esc($st['status_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assigned To / PIC (Bisa Pilih Banyak)</label>
                            <select name="assigned_to[]" class="form-select" multiple style="height: 100px;">
                                <?php foreach ($previewUsers as $u) : ?>
                                    <option value="<?= $u['id'] ?>" <?= ((int) session()->get('user_id') === (int) $u['id']) ? 'selected' : '' ?>><?= esc($u['name']) ?> (<?= esc($u['job_title'] ?? 'Staff') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted fs-7">*User login otomatis menjadi ID pertama di assigned_to.</small>
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
                    <button type="<?= $isPreviewMode ? 'button' : 'submit' ?>" class="btn btn-primary" <?= $isPreviewMode ? 'data-bs-dismiss="modal"' : '' ?>><i class="bi bi-save me-1"></i> Simpan Proyek</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($displayProjects as $prj) : ?>
    <?php if (empty($prj['is_preview'])) {
        continue;
    } ?>

    <div class="modal fade" id="modalDetailProject<?= esc($prj['id']) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-eye-fill me-2"></i>Detail Project</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <span class="badge bg-light-primary text-primary mb-2"><?= esc($prj['project_code']) ?></span>
                        <h5 class="fw-bold mb-1"><?= esc($prj['name']) ?></h5>
                        <span class="badge <?= match ($prj['status']) {
                            'Planning' => 'bg-secondary',
                            'Defining' => 'bg-info',
                            'Designing' => 'bg-primary',
                            'Building' => 'bg-warning text-dark',
                            'Testing' => 'bg-danger',
                            'Deployment' => 'bg-success',
                            default => 'bg-secondary',
                        } ?>"><?= esc($prj['status']) ?></span>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Start Date</small>
                            <strong><?= !empty($prj['start_date']) ? date('d M Y', strtotime($prj['start_date'])) : '-' ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">End Date</small>
                            <strong><?= !empty($prj['end_date']) ? date('d M Y', strtotime($prj['end_date'])) : '-' ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Unit Testing</small>
                            <strong><?= !empty($prj['unit_testing_date']) ? date('d M Y', strtotime($prj['unit_testing_date'])) : '-' ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">SIT</small>
                            <strong><?= !empty($prj['sit_date']) ? date('d M Y', strtotime($prj['sit_date'])) : '-' ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">UAT</small>
                            <strong><?= !empty($prj['uat_date']) ? date('d M Y', strtotime($prj['uat_date'])) : '-' ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Promote</small>
                            <strong><?= !empty($prj['promote_date']) ? date('d M Y', strtotime($prj['promote_date'])) : '-' ?></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Assigned To / PIC</small>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($prj['assigned_users'] as $assignedUser) : ?>
                                <span class="badge bg-light-primary text-primary">
                                    <i class="bi bi-person-fill me-1"></i><?= esc($assignedUser['name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <small class="text-muted d-block mb-1">Catatan</small>
                    <p class="mb-0"><?= esc($prj['notes']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditProject<?= esc($prj['id']) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form>
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark"><i class="bi bi-pencil-square me-2"></i>Edit Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Project Code</label>
                                <input type="text" class="form-control" value="<?= esc($prj['project_code']) ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Name Project</label>
                                <input type="text" class="form-control" value="<?= esc($prj['name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Project Status</label>
                                <select class="form-select">
                                    <?php foreach ($statusOptions as $st) : ?>
                                        <option value="<?= esc($st['id']) ?>" <?= $prj['status'] === $st['status_name'] ? 'selected' : '' ?>><?= esc($st['status_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Assigned To / PIC</label>
                                <select class="form-select" multiple style="height: 100px;">
                                    <?php foreach ($previewUsers as $u) : ?>
                                        <option value="<?= esc($u['id']) ?>" <?= in_array($u, $prj['assigned_users'], true) ? 'selected' : '' ?>>
                                            <?= esc($u['name']) ?> (<?= esc($u['job_title'] ?? 'Staff') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" class="form-control" value="<?= esc($prj['start_date']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" class="form-control" value="<?= esc($prj['end_date']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-sm">Unit Testing</label>
                                <input type="date" class="form-control" value="<?= esc($prj['unit_testing_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-sm">SIT Date</label>
                                <input type="date" class="form-control" value="<?= esc($prj['sit_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-sm">UAT Date</label>
                                <input type="date" class="form-control" value="<?= esc($prj['uat_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-sm">Promote</label>
                                <input type="date" class="form-control" value="<?= esc($prj['promote_date'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Catatan Proyek</label>
                                <textarea class="form-control" rows="3"><?= esc($prj['notes']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal"><i class="bi bi-check-circle me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?= $this->endSection() ?>
