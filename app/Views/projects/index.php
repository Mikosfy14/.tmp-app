<?php
/**
 * @var array $statusOptions
 * @var array $projects
 * @var string|null $selectedStatus
 * @var bool $isFilteredUser
 * @var array|null $targetUser
 */

$displayProjects = $projects ?? [];
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
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                        <input type="text" id="projectSearch" class="form-control" placeholder="Cari kode, nama project, atau PIC...">
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4">
                    <select id="projectStatusFilter" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <?php foreach ($statusOptions as $st) : ?>
                            <option value="<?= esc($st['status_name']) ?>"><?= esc($st['status_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <button type="button" id="projectFilterReset" class="btn btn-outline-secondary w-100">Reset</button>
                </div>
            </div>
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
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('projectSearch');
        const statusFilter = document.getElementById('projectStatusFilter');
        const resetButton = document.getElementById('projectFilterReset');
        const rows = Array.from(document.querySelectorAll('.project-row'));
        const emptyRow = document.getElementById('projectsEmptyRow');

        if (!searchInput || !statusFilter || !resetButton || !emptyRow) {
            return;
        }

        function applyProjectFilters() {
            const keyword = (searchInput.value || '').trim().toLowerCase();
            const status = statusFilter.value;
            let visibleCount = 0;

            rows.forEach(function(row) {
                const matchesSearch = !keyword || row.dataset.search.includes(keyword);
                const matchesStatus = !status || row.dataset.status === status;
                const isVisible = matchesSearch && matchesStatus;

                row.classList.toggle('d-none', !isVisible);
                if (isVisible) {
                    visibleCount++;
                }
            });

            emptyRow.classList.toggle('d-none', visibleCount > 0);
        }

        searchInput.addEventListener('input', applyProjectFilters);
        statusFilter.addEventListener('change', applyProjectFilters);
        resetButton.addEventListener('click', function() {
            searchInput.value = '';
            statusFilter.value = '';
            applyProjectFilters();
        });
    });
</script>

<?= $this->endSection() ?>
