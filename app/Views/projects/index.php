<?php

/**
 * @var array $statusOptions
 * @var array $projects
 * @var string|null $selectedStatus
 * @var string|null $selectedIsCompleted
 * @var string|null $selectedStartDate
 * @var string|null $selectedEndDate
 * @var bool $isFilteredUser
 * @var array|null $targetUser
 * @var \CodeIgniter\Pager\Pager|null $pager
 * @var bool|null $isKadept
 * @var string|null $scope
 * @var int|null $countMyProjects
 * @var int|null $countAllProjects
 */

helper('deadline');

$displayProjects = $projects ?? [];
$selectedStartDate = (string) ($selectedStartDate ?? '');
$selectedEndDate = (string) ($selectedEndDate ?? '');
$selectedIsCompleted = (string) ($selectedIsCompleted ?? '');
$isKadept = (bool) ($isKadept ?? false);
$scope = (string) ($scope ?? 'my');
$countMyProjects = (int) ($countMyProjects ?? 0);
$countAllProjects = (int) ($countAllProjects ?? 0);
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .project-scope-pills {
        background-color: #ffffff;
    }

    [data-bs-theme="dark"] .project-scope-pills {
        background-color: var(--bs-card-bg, #1e1e2d) !important;
        border-color: var(--bs-border-color) !important;
    }

    .project-scope-pills .nav-link {
        border-radius: 8px;
        transition: all .2s ease-in-out;
    }

    .project-scope-pills .nav-link.active {
        background-color: #435ebe;
        color: #ffffff !important;
    }

    [data-bs-theme="dark"] .project-scope-pills .nav-link.active .badge {
        background-color: #ffffff !important;
        color: #435ebe !important;
    }

    .project-pagination .pagination {
        margin: 0;
        gap: .35rem;
    }

    .project-pagination .page-item .page-link {
        border: 0;
        border-radius: .55rem;
        min-width: 2.25rem;
        text-align: center;
        color: #52606d;
        font-weight: 600;
    }

    .project-pagination .page-item.active .page-link {
        background: #435ebe;
        color: #fff;
        box-shadow: 0 .25rem .65rem rgba(67, 94, 190, .25);
    }

    .project-pagination .page-item:not(.active) .page-link:hover {
        background: #eef1ff;
        color: #435ebe;
    }

    .project-pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background: #f1f3f5;
        opacity: .75;
        cursor: not-allowed;
        pointer-events: none;
    }

    [data-bs-theme="dark"] .project-pagination .page-item:not(.active) .page-link {
        color: #a0aec0;
        background: transparent;
    }

    [data-bs-theme="dark"] .project-pagination .page-item:not(.active) .page-link:hover {
        background: rgba(67, 94, 190, 0.2);
        color: #8fa0f0;
    }

    [data-bs-theme="dark"] .project-pagination .page-item.disabled .page-link {
        color: #607080;
        background: rgba(255, 255, 255, 0.05);
    }

    .period-filter-group .form-control[readonly] {
        background-color: var(--bs-body-bg);
        cursor: pointer;
    }

    .filter-reset-button {
        width: 100%;
        min-width: 0;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .filter-reset-button i {
        line-height: 1;
        font-size: 1.05rem;
    }

    .filter-submit-button {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        line-height: 1;
    }

    @media (max-width: 767.98px) {
        .project-page-heading {
            flex-direction: column;
            align-items: flex-start !important;
            gap: .85rem;
        }

        .project-page-heading>a {
            align-self: flex-start;
        }
    }

    @media (max-width: 991.98px) {
        .filter-reset-button {
            width: 100%;
            min-height: 38px;
            padding-inline: .75rem;
        }
    }

    .flatpickr-calendar {
        border: 1px solid var(--bs-border-color);
        box-shadow: 0 .75rem 2rem rgba(30, 30, 45, .18);
    }

    .flatpickr-day.inRange,
    .flatpickr-day.prevMonthDay.inRange,
    .flatpickr-day.nextMonthDay.inRange {
        background: rgba(67, 94, 190, .16);
        border-color: rgba(67, 94, 190, .08);
        box-shadow: -5px 0 0 rgba(67, 94, 190, .16), 5px 0 0 rgba(67, 94, 190, .16);
    }

    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #435ebe;
        border-color: #435ebe;
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

    [data-bs-theme="dark"] .flatpickr-day.flatpickr-disabled,
    [data-bs-theme="dark"] .flatpickr-day.prevMonthDay,
    [data-bs-theme="dark"] .flatpickr-day.nextMonthDay {
        color: #607080;
    }

    .project-actions {
        width: 1%;
        white-space: nowrap;
    }

    .project-action-group {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: .35rem;
        flex-wrap: nowrap;
    }

    .project-action-group .btn {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        white-space: nowrap;
    }

    .project-action-group .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .project-action-group .btn-outline-danger:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .project-delete-form {
        display: inline-flex;
        margin: 0;
    }

    .project-delete-btn-cell {
        display: inline-flex;
        align-items: center;
    }

    .project-delete-btn-cell .btn {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        white-space: nowrap;
        color: #dc3545;
        border-color: #dc3545;
    }

    .project-delete-btn-cell .btn:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .project-delete-btn-cell .btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .project-row {
        transition: background-color .15s ease-in-out;
    }

    .project-row.table-active {
        background-color: rgba(67, 94, 190, .08) !important;
    }

    .project-progress-preview {
        min-width: 140px;
    }

    .project-progress-track {
        height: 6px;
        border-radius: 999px;
        background-color: rgba(108, 117, 125, .18);
        overflow: hidden;
    }

    .project-progress-bar {
        height: 100%;
        border-radius: 999px;
        transition: width .2s ease;
    }

    .project-progress-label {
        font-size: .72rem;
        line-height: 1;
        margin-top: .25rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4 project-page-heading">
    <div>
        <h3 class="mb-1">
            <?php if (!empty($isFilteredUser) && !empty($targetUser)) : ?>
                Project Tracker - <?= esc($targetUser['name']) ?>
            <?php else : ?>
                Project Tracker
            <?php endif; ?>
        </h3>
        <p class="text-muted mb-0">
            <?php if (!empty($isFilteredUser) && !empty($targetUser)) : ?>
                Menampilkan seluruh project yang ditugaskan kepada <strong><?= esc($targetUser['name']) ?></strong> (<?= esc($targetUser['job_title'] ?: 'Staff') ?>).
                <a href="<?= base_url('/projects') ?>" class="ms-2 text-primary fw-semibold"><i class="bi bi-arrow-left me-1"></i>Kembali ke Semua Project</a>
            <?php elseif ($isKadept) : ?>
                <?= $scope === 'all' ? 'Memantau seluruh proyek yang dikelola oleh tim departemen.' : 'Menampilkan proyek yang secara spesifik ditugaskan kepada Anda.' ?>
            <?php else : ?>
                Kelola dan pantau project yang ditugaskan kepada Anda.
            <?php endif; ?>
        </p>
    </div>
    <?php
    $exportParams = [];
    if (!empty($selectedStatus)) $exportParams['status'] = $selectedStatus;
    if (!empty($selectedIsCompleted)) $exportParams['is_completed'] = $selectedIsCompleted;
    if (!empty($keyword)) $exportParams['keyword'] = $keyword;
    if (!empty($selectedStartDate)) $exportParams['filter_start'] = $selectedStartDate;
    if (!empty($selectedEndDate)) $exportParams['filter_end'] = $selectedEndDate;
    if (!empty($isFilteredUser) && !empty($targetUser)) {
        $exportParams['user_id'] = $targetUser['id'];
    } elseif ($isKadept) {
        $exportParams['scope'] = $scope;
    }
    $exportQueryString = !empty($exportParams) ? '?' . http_build_query($exportParams) : '';
    ?>
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary text-body d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-export text-body"></i>
                <span>Ekspor Data</span>
                <i class="fas fa-chevron-down text-body" style="font-size: 0.7rem; transform: translateY(1px);"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= base_url('/projects/export/excel' . $exportQueryString) ?>">
                        <i class="fas fa-file-excel text-success fs-5"></i>
                        <div>
                            <strong class="d-block text-dark">Download Excel (.xlsx)</strong>
                        </div>
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider my-1">
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= base_url('/projects/export/pdf' . $exportQueryString) ?>" target="_blank">
                        <i class="fas fa-file-pdf text-danger fs-5"></i>
                        <div>
                            <strong class="d-block text-dark">Download PDF (.pdf)</strong>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
        <a href="<?= base_url('/projects/create') ?>" class="btn btn-primary d-flex align-items-center gap-1">
            <i class="fas fa-plus-square me-1"></i> Tambah Project
        </a>
    </div>
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
    <?php if ($isKadept && empty($isFilteredUser)) : ?>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <ul class="nav nav-pills project-scope-pills p-1 rounded-3 border" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $scope === 'my' ? 'active fw-bold' : 'text-body' ?> d-inline-flex align-items-center gap-2 py-2 px-3" href="<?= base_url('/projects?scope=my') ?>">
                        <span>Project Saya</span>
                        <span class="badge rounded-pill <?= $scope === 'my' ? 'bg-light text-primary' : 'bg-secondary text-white' ?> ms-1"><?= $countMyProjects ?></span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $scope === 'all' ? 'active fw-bold' : 'text-body' ?> d-inline-flex align-items-center gap-2 py-2 px-3" href="<?= base_url('/projects?scope=all') ?>">
                        <span>Semua Project Tim</span>
                        <span class="badge rounded-pill <?= $scope === 'all' ? 'bg-light text-primary' : 'bg-secondary text-white' ?> ms-1"><?= $countAllProjects ?></span>
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="get" action="<?= base_url(!empty($isFilteredUser) && !empty($targetUser) ? '/projects/user/' . $targetUser['id'] : '/projects') ?>" class="row g-2 align-items-center">
                <input type="hidden" name="page_projects" value="1">
                <?php if ($isKadept && empty($isFilteredUser)) : ?>
                    <input type="hidden" name="scope" value="<?= esc($scope) ?>">
                <?php endif; ?>
                <div class="col-12 col-lg-3">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control" placeholder="Cari kode, nama, atau PIC..." value="<?= esc($keyword ?? '') ?>">
                        <span class="input-group-text bg-transparent d-flex align-items-center" aria-hidden="true"><i class="bi bi-search lh-1"></i></span>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <select name="status" class="form-select">
                        <option value="">All SDLC Status</option>
                        <?php foreach ($statusOptions as $st) : ?>
                            <option value="<?= esc($st['id']) ?>" <?= (string) ($selectedStatus ?? '') === (string) $st['id'] ? 'selected' : '' ?>><?= esc($st['status_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <select name="is_completed" class="form-select">
                        <option value="">All</option>
                        <option value="completed" <?= $selectedIsCompleted === 'completed' || $selectedIsCompleted === '1' ? 'selected' : '' ?>>Completed</option>
                        <option value="not_completed" <?= $selectedIsCompleted === 'not_completed' || $selectedIsCompleted === '0' ? 'selected' : '' ?>>Not Completed</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="period_picker" class="visually-hidden">Filter tanggal</label>
                    <div class="input-group period-filter-group">
                        <input type="text" id="period_picker" class="form-control" placeholder="Date Range" readonly>
                        <span class="input-group-text bg-transparent d-flex align-items-center" aria-hidden="true"><i class="bi bi-calendar3 lh-1"></i></span>
                    </div>
                    <input type="hidden" name="filter_start" id="filter_start" value="<?= esc($selectedStartDate) ?>">
                    <input type="hidden" name="filter_end" id="filter_end" value="<?= esc($selectedEndDate) ?>">
                </div>
                <div class="col-6 col-md-3 col-lg-1 d-flex">
                    <button type="submit" class="btn btn-primary filter-submit-button w-100 px-2" title="Terapkan filter" aria-label="Terapkan filter">
                        <i class="bi bi-search" aria-hidden="true"></i><span class="d-inline d-lg-none ms-1">Cari</span>
                    </button>
                </div>
                <div class="col-6 col-md-3 col-lg-1 d-flex justify-content-lg-end">
                    <?php
                    $resetUrl = '/projects';
                    if (!empty($isFilteredUser) && !empty($targetUser)) {
                        $resetUrl = '/projects/user/' . $targetUser['id'];
                    } elseif ($isKadept && $scope === 'all') {
                        $resetUrl = '/projects?scope=all';
                    }
                    ?>
                    <a href="<?= base_url($resetUrl) ?>" class="btn btn-outline-secondary filter-reset-button" title="Reset filter" aria-label="Reset filter">
                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i><span class="d-inline d-lg-none ms-1">Reset</span>
                    </a>
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
                                    $prj['database_type_name'] ?? '',
                                    implode(' ', $assignedNames),
                                ])));
                                $isCompletedPrj = is_project_completed($prj);
                                $deadline = get_deadline_status($prj['end_date'] ?? null, $isCompletedPrj);

                                $relativeDeadlineText = null;
                                $relativeDeadlineClass = 'text-muted';
                                if (!$isCompletedPrj && !empty($prj['end_date'])) {
                                    try {
                                        $today = new DateTimeImmutable(date('Y-m-d'));
                                        $targetDate = new DateTimeImmutable(date('Y-m-d', strtotime($prj['end_date'])));
                                        $diff = (int) $today->diff($targetDate)->format('%r%a');
                                        if ($diff < 0) {
                                            $relativeDeadlineText = abs($diff) . ' hari terlambat';
                                            $relativeDeadlineClass = 'text-danger fw-semibold';
                                        } elseif ($diff === 0) {
                                            $relativeDeadlineText = 'Tenggat hari ini';
                                            $relativeDeadlineClass = 'text-danger fw-bold';
                                        } else {
                                            $relativeDeadlineText = 'Sisa ' . $diff . ' hari';
                                            $relativeDeadlineClass = !empty($deadline['class']) ? 'text-' . esc($deadline['class']) . ' fw-semibold' : 'text-muted';
                                        }
                                    } catch (\Exception $e) {
                                        $relativeDeadlineText = null;
                                    }
                                }
                                ?>
                                <tr class="project-row"
                                    data-search="<?= esc($searchText) ?>"
                                    data-status="<?= esc($prj['status'] ?? '') ?>">
                                    <td>
                                        <strong class="text-dark d-block"><?= esc($prj['name']) ?></strong>
                                        <div class="d-flex align-items-center flex-wrap gap-1 mt-1">
                                            <span class="badge bg-light-secondary text-muted"><?= esc($prj['project_code']) ?></span>
                                            <?php if (!empty($prj['database_type_name'])) : ?>
                                                <span class="badge bg-light-info text-info border border-info-subtle" title="Tipe Database">
                                                    <i class="bi bi-database me-1"></i><?= esc($prj['database_type_name']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusBadge ?>"><?= esc($prj['status'] ?? '-') ?></span>
                                        <span class="badge bg-light-<?= esc($deadline['class']) ?> text-<?= esc($deadline['class']) ?> d-block mt-2" style="width: fit-content;">
                                            <?= esc($deadline['label']) ?>
                                        </span>
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
                                        <?php if ($relativeDeadlineText !== null) : ?>
                                            <small class="d-block <?= $relativeDeadlineClass ?>" style="font-size: 0.72rem;"><?= esc($relativeDeadlineText) ?></small>
                                        <?php endif; ?>
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
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Data project tidak tersedia
                                </td>
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

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pickerInput = document.getElementById('period_picker');
        const startInput = document.getElementById('filter_start');
        const endInput = document.getElementById('filter_end');
        const clearButton = document.getElementById('clear_period');

        if (!pickerInput || !startInput || !endInput || typeof flatpickr === 'undefined') {
            return;
        }

        const defaultDates = startInput.value && endInput.value ? [startInput.value, endInput.value] : [];
        const getNextDate = function(date) {
            const nextDate = new Date(date.getTime());
            nextDate.setDate(nextDate.getDate() + 1);

            return nextDate;
        };
        const picker = flatpickr(pickerInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            defaultDate: defaultDates,
            locale: flatpickr.l10ns.id,
            showMonths: 1,
            onChange: function(selectedDates, dateString, instance) {
                if (selectedDates.length === 0) {
                    startInput.value = '';
                    endInput.value = '';
                    return;
                }

                startInput.value = instance.formatDate(selectedDates[0], 'Y-m-d');

                if (selectedDates.length === 1) {
                    endInput.value = instance.formatDate(getNextDate(selectedDates[0]), 'Y-m-d');
                    return;
                }

                endInput.value = instance.formatDate(selectedDates[1], 'Y-m-d');
            }
        });

        if (clearButton) {
            clearButton.addEventListener('click', function() {
                picker.clear();
                startInput.value = '';
                endInput.value = '';
            });
        }
    });
</script>
<?= $this->endSection() ?>