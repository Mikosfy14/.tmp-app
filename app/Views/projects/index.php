<?php
/**
 * @var array $statusOptions
 * @var array $projects
 * @var string|null $selectedStatus
 * @var int $selectedYear
 * @var string|null $selectedQuarter
 * @var array $yearOptions
 * @var bool $isFilteredUser
 * @var array|null $targetUser
 * @var \CodeIgniter\Pager\Pager|null $pager
 */

$displayProjects = $projects ?? [];
$yearOptions = $yearOptions ?? [(int) date('Y')];
$selectedYear = (int) ($selectedYear ?? date('Y'));
$selectedQuarter = (string) ($selectedQuarter ?? '');
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

    .project-pagination .pagination,
    .user-pagination .pagination {
        margin: 0;
        gap: .35rem;
    }

    .project-pagination .page-item .page-link,
    .user-pagination .page-item .page-link {
        border: 0;
        border-radius: .55rem;
        min-width: 2.25rem;
        text-align: center;
        color: #52606d;
        font-weight: 600;
    }

    .project-pagination .page-item.active .page-link,
    .user-pagination .page-item.active .page-link {
        background: #435ebe;
        color: #fff;
    }

    .project-pagination .page-item:not(.active),
    .user-pagination .page-item:not(.active) .page-link {
        color: #435ebe;
    }

    .project-pagination .page-item.disabled .page-link,
    .user-pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background: #f1f3f5;
        opacity: .80;
        cursor: not-allowed;
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
    <a href="<?= base_url('/projects/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Project
    </a>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="page-content">
    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="get" action="<?= base_url(!empty($isFilteredUser) && !empty($targetUser) ? '/projects/user/' . $targetUser['id'] : '/projects') ?>" class="row g-2 align-items-center">
                <input type="hidden" name="page_projects" value="1">
                <div class="col-12 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                        <input type="text" name="keyword" class="form-control" placeholder="Cari kode, nama project, atau PIC..." value="<?= esc($keyword ?? '') ?>">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <?php foreach ($statusOptions as $st) : ?>
                            <option value="<?= esc($st['id']) ?>" <?= (string) ($selectedStatus ?? '') === (string) $st['id'] ? 'selected' : '' ?>><?= esc($st['status_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 col-lg-1">
                    <select name="year" class="form-select">
                        <?php foreach ($yearOptions as $year) : ?>
                            <option value="<?= esc($year) ?>" <?= $selectedYear === (int) $year ? 'selected' : '' ?>>
                                <?= esc($year) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <select name="quarter" class="form-select">
                        <option value="">Semua Triwulan</option>
                        <option value="1" <?= $selectedQuarter === '1' ? 'selected' : '' ?>>Triwulan I</option>
                        <option value="2" <?= $selectedQuarter === '2' ? 'selected' : '' ?>>Triwulan II</option>
                        <option value="3" <?= $selectedQuarter === '3' ? 'selected' : '' ?>>Triwulan III</option>
                        <option value="4" <?= $selectedQuarter === '4' ? 'selected' : '' ?>>Triwulan IV</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
                <div class="col-12 col-md-6 col-lg-1">
                    <a href="<?= base_url(!empty($isFilteredUser) && !empty($targetUser) ? '/projects/user/' . $targetUser['id'] : '/projects') ?>" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="projectsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Kode & Nama Project</th>
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
                                $statusBadge = match ($prj['status'] ?? '') {
                                    'Planning' => 'bg-secondary',
                                    'Defining' => 'bg-info',
                                    'Designing' => 'bg-primary',
                                    'Building' => 'bg-warning text-dark',
                                    'Testing' => 'bg-danger',
                                    'Deployment' => 'bg-success',
                                    default => 'bg-secondary',
                                };
                                $assignedNames = [];
                                if (!empty($prj['assigned_users'])) {
                                    foreach ($prj['assigned_users'] as $assignedUser) {
                                        $assignedNames[] = $assignedUser['name'] ?? '';
                                    }
                                }
                                $searchText = strtolower(implode(' ', array_filter([
                                    $prj['project_code'] ?? '',
                                    $prj['name'] ?? '',
                                    implode(' ', $assignedNames),
                                ])));
                                ?>
                                <tr class="project-row"
                                    data-search="<?= esc($searchText) ?>"
                                    data-status="<?= esc($prj['status'] ?? '') ?>">
                                    <td>
                                        <strong class="text-dark d-block"><?= esc($prj['name']) ?></strong>
                                        <span class="badge bg-light-secondary text-muted"><?= esc($prj['project_code']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusBadge ?>"><?= esc($prj['status'] ?? '-') ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($prj['assigned_users'])) : ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach ($prj['assigned_users'] as $assignedUser) : ?>
                                                    <span class="badge bg-light-primary text-primary" title="<?= esc($assignedUser['job_title'] ?? '') ?>">
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
                                            <a href="<?= base_url('/projects/detail/' . $prj['id']) ?>" class="btn btn-sm btn-outline-primary" title="Detail Project">
                                                <i class="bi bi-eye-fill"></i> Detail
                                            </a>
                                            <a href="<?= base_url('/projects/edit/' . $prj['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit Project">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr id="projectsEmptyRow">
                                <td colspan="5" class="text-center py-4 text-muted">Data project tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($displayProjects)) : ?>
                            <tr id="projectsEmptyRow" class="d-none">
                                <td colspan="5" class="text-center py-4 text-muted">Data project tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($displayProjects) && !empty($pager) && $pager->getPageCount('projects') > 1) : ?>
                <div class="project-pagination d-flex justify-content-end p-3 border-top">
                    <?= $pager->links('projects', 'complete') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
