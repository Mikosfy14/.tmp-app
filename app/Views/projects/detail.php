<?php

/**
 * @var array $project
 * @var array $projectFiles
 * @var array $allowedLogTypes
 * @var array $logbooks
 * @var int $currentUserId
 */

helper(['deadline', 'navigation']);
$backNav = get_contextual_back('/projects', 'Kembali ke Project Tracker');

$isKadept = (strtolower((string) session()->get('role_name')) === 'kepala departemen') || ((int) session()->get('role_id') === 1);

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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            text-align: center;
            min-height: 44px;
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
            width: 44px !important;
            height: 44px !important;
            min-height: 44px !important;
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

    .pic-scrollable-list {
        max-height: 180px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .file-scrollable-list {
        max-height: 180px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .notes-scrollable-container {
        max-height: 380px;
        overflow-y: auto;
    }

    .pic-scrollable-list::-webkit-scrollbar,
    .file-scrollable-list::-webkit-scrollbar,
    .notes-scrollable-container::-webkit-scrollbar {
        width: 5px;
    }

    .pic-scrollable-list::-webkit-scrollbar-track,
    .file-scrollable-list::-webkit-scrollbar-track,
    .notes-scrollable-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .pic-scrollable-list::-webkit-scrollbar-thumb,
    .file-scrollable-list::-webkit-scrollbar-thumb,
    .notes-scrollable-container::-webkit-scrollbar-thumb {
        background: rgba(108, 117, 125, 0.3);
        border-radius: 4px;
    }

    .pic-scrollable-list::-webkit-scrollbar-thumb:hover,
    .file-scrollable-list::-webkit-scrollbar-thumb:hover,
    .notes-scrollable-container::-webkit-scrollbar-thumb:hover {
        background: rgba(108, 117, 125, 0.5);
    }

    /* Logbook Progres Mingguan - Native Enterprise Styling */
    .logbook-feed-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .logbook-item-card {
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color, #e9ecef);
        border-radius: 10px;
        padding: 1.25rem;
        transition: border-color 0.2s ease;
    }

    [data-bs-theme="dark"] .logbook-item-card {
        background-color: #252539 !important;
        border-color: #2b2b40 !important;
    }

    .logbook-item-card.is-kadept {
        border-left: 3px solid #435ebe;
    }

    [data-bs-theme="dark"] .logbook-item-card.is-kadept {
        border-left-color: #8fa0f0;
    }

    .logbook-item-card.is-team-staff {
        border-left: 3px solid #198754;
    }

    [data-bs-theme="dark"] .logbook-item-card.is-team-staff {
        border-left-color: #5dd593;
    }

    .logbook-item-card.is-team-manmonth {
        border-left: 3px solid #ffc107;
    }

    [data-bs-theme="dark"] .logbook-item-card.is-team-manmonth {
        border-left-color: #f7d070;
    }

    .logbook-callout-blocker {
        background-color: rgba(220, 53, 69, 0.05);
        border: 1px solid rgba(220, 53, 69, 0.2);
        border-left: 3px solid #dc3545;
        border-radius: 6px;
        padding: 0.75rem 1rem;
    }

    [data-bs-theme="dark"] .logbook-callout-blocker {
        background-color: rgba(220, 53, 69, 0.12) !important;
        border-color: rgba(220, 53, 69, 0.25) !important;
        border-left-color: #f87171 !important;
    }

    .logbook-callout-kadept {
        background-color: rgba(67, 94, 190, 0.05);
        border: 1px solid rgba(67, 94, 190, 0.2);
        border-left: 3px solid #435ebe;
        border-radius: 6px;
        padding: 0.75rem 1rem;
    }

    [data-bs-theme="dark"] .logbook-callout-kadept {
        background-color: rgba(67, 94, 190, 0.12) !important;
        border-color: rgba(67, 94, 190, 0.25) !important;
        border-left-color: #8fa0f0 !important;
    }

    .logbook-filter-btn.active {
        background-color: #435ebe !important;
        color: #fff !important;
        border-color: #435ebe !important;
    }

    .logbook-richtext-content {
        font-size: 0.875rem;
        color: var(--bs-body-color);
        line-height: 1.55;
    }

    .logbook-richtext-content p {
        margin-bottom: 0.35rem;
    }

    .logbook-richtext-content p:last-child {
        margin-bottom: 0;
    }

    .logbook-richtext-content ul,
    .logbook-richtext-content ol {
        margin-bottom: 0.35rem;
        padding-left: 1.25rem;
    }

    .logbook-richtext-content ul:last-child,
    .logbook-richtext-content ol:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 767.98px) {
        .logbook-item-card {
            padding: 1rem;
        }

        .logbook-card-header {
            align-items: stretch !important;
        }

        .logbook-card-header > div:first-child,
        .logbook-card-header > div:last-child {
            width: 100%;
        }

        .logbook-card-header > div:last-child .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 44px;
        }

        .logbook-filter-row {
            display: block !important;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }

        .logbook-filter-group {
            display: inline-flex;
            flex-wrap: nowrap;
            min-width: max-content;
        }

        .logbook-filter-group .btn {
            min-height: 44px;
            white-space: nowrap;
        }

        .logbook-entry-header {
            align-items: flex-start !important;
        }

        .logbook-entry-header > .d-flex:first-child {
            align-items: flex-start;
            flex: 1 1 100%;
            min-width: 0;
            flex-wrap: wrap;
        }

        .logbook-entry-header > .d-flex:first-child > div:last-child {
            min-width: 0;
        }

        .logbook-entry-actions {
            width: 100%;
            padding-top: 0.25rem;
        }

        .logbook-entry-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            flex: 1 1 auto;
        }

        .logbook-entry-content {
            gap: 1.25rem !important;
        }

        .logbook-entry-footer {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 0.35rem !important;
        }
    }

    .highlight-pulse {
        animation: pulseHighlight 2s ease-out;
    }

    @keyframes pulseHighlight {
        0% {
            box-shadow: 0 0 0 0 rgba(67, 94, 190, 0.7);
            border-color: #435ebe;
        }

        50% {
            box-shadow: 0 0 0 10px rgba(67, 94, 190, 0);
            border-color: #435ebe;
        }

        100% {
            box-shadow: 0 0 0 0 rgba(67, 94, 190, 0);
        }
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
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <?= esc(session()->getFlashdata('error')) ?>
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
    <!-- Milestone Track (Unit Testing -> SIT -> UAT -> Promote) - Full Width -->
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

    <!-- Justified Equal-Height Row: Catatan Proyek (Left) vs Tim Penanggung Jawab & Berkas Proyek (Right) -->
    <div class="row g-4 mb-4 align-items-stretch">
        <!-- Catatan Proyek (Left Column) -->
        <div class="col-12 col-lg-7 d-flex flex-column">
            <div class="card shadow-sm h-100 d-flex flex-column mb-0">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Catatan Proyek</h5>
                </div>
                <div class="card-body pt-3 d-flex flex-column flex-grow-1">
                    <?php if (!empty(trim((string) ($project['notes'] ?? '')))) : ?>
                        <div class="notes-container notes-scrollable-container text-body flex-grow-1">
                            <?= nl2br(esc($project['notes'])) ?>
                        </div>
                    <?php else : ?>
                        <div class="text-muted small py-2 flex-grow-1">
                            Tidak ada catatan tambahan untuk project ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tim Penanggung Jawab & Berkas Proyek (Right Column) -->
        <div class="col-12 col-lg-5 d-flex flex-column justify-content-between gap-4">
            <!-- Assigned Team (PIC) -->
            <div class="card shadow-sm mb-0">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Tim Penanggung Jawab</h5>
                </div>
                <div class="card-body pt-3">
                    <?php if (!empty($project['assigned_users'])) : ?>
                        <?php
                        $primaryPicId = (int) (explode(',', (string) ($project['assigned_to'] ?? ''))[0] ?? 0);
                        $isPicScrollable = count($project['assigned_users']) > 3;
                        ?>
                        <div class="d-flex flex-column <?= $isPicScrollable ? 'pic-scrollable-list' : '' ?>">
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
            <div class="card shadow-sm mb-0">
                <div class="card-header pb-0 border-0">
                    <h5 class="card-title mb-0 fs-6 fw-bold">Berkas Proyek</h5>
                </div>
                <div class="card-body pt-3">
                    <?php if (!empty($projectFiles)) : ?>
                        <?php $isFileScrollable = count($projectFiles) > 3; ?>
                        <div class="d-flex flex-column <?= $isFileScrollable ? 'file-scrollable-list' : '' ?>">
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

    <?php
    $logbooks = is_array($logbooks ?? null) ? $logbooks : [];
    $allowedLogTypes = is_array($allowedLogTypes ?? null) ? $allowedLogTypes : [];
    $logbookCounts = [
        'all' => count($logbooks),
        'kadept' => count(array_filter($logbooks, static fn (array $item): bool => ($item['log_type'] ?? '') === 'kadept_review')),
        'team' => count(array_filter($logbooks, static fn (array $item): bool => ($item['log_type'] ?? '') === 'team_log')),
    ];
    $canCreateLogbook = !empty($allowedLogTypes);
    ?>

    <!-- Logbook Progres Mingguan Section (Full Width) -->
    <?php if (isset($logbooks)) : ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header logbook-card-header pb-0 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="card-title mb-0 fs-6 fw-bold">Logbook Progres Mingguan</h5>
                <span class="text-muted small">Riwayat evaluasi mingguan & laporan progres pengerjaan</span>
            </div>
            <?php if ($canCreateLogbook) : ?>
                <div>
                    <a href="<?= base_url('/projects/' . $project['id'] . '/logbooks/create') ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Log Mingguan
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-body pt-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 logbook-filter-row">
                <div class="btn-group btn-group-sm logbook-filter-group" role="group" id="logbookFilterGroup">
                    <button type="button" class="btn btn-outline-secondary logbook-filter-btn active" data-filter="all">
                        Semua <span class="badge bg-secondary ms-1"><?= $logbookCounts['all'] ?></span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary logbook-filter-btn" data-filter="kadept">
                        Review Kadept <span class="badge bg-primary ms-1"><?= $logbookCounts['kadept'] ?></span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary logbook-filter-btn" data-filter="team">
                        Laporan Tim <span class="badge bg-success ms-1"><?= $logbookCounts['team'] ?></span>
                    </button>
                </div>
            </div>

            <?php if (empty($logbooks)) : ?>
                <div class="text-center text-muted py-4">
                    Belum ada Logbook pada project ini.
                </div>
            <?php else : ?>
                <div class="logbook-feed-container" id="logbookFeed">
                    <?php foreach ($logbooks as $log) :
                        $isTeamLog = ($log['log_type'] ?? '') === 'team_log';
                        $logTypeClass = $isTeamLog ? 'team' : 'kadept';
                        $isAuthor = (int) ($log['user_id'] ?? 0) === (int) ($currentUserId ?? 0);
                        $canEditLog = $isAuthor && in_array($log['log_type'] ?? '', $allowedLogTypes, true);
                        $displayRole = $log['role_name'] ?? ($isTeamLog ? 'PIC' : 'Kepala Departemen');
                        $displayDate = !empty($log['log_date']) ? date('d M Y', strtotime($log['log_date'])) : '-';
                        $updatedDate = !empty($log['updated_at']) ? date('d M Y, H:i', strtotime($log['updated_at'])) : $displayDate;
                        $avatar = strtoupper(substr((string) ($log['author_name'] ?? 'U'), 0, 2));
                    ?>
                        <div class="logbook-item-card <?= $isTeamLog ? 'is-team-staff' : 'is-kadept' ?> logbook-entry-card-wrapper" id="logbook-<?= (int) $log['id'] ?>" data-type="<?= esc($logTypeClass, 'attr') ?>">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3 pb-2 border-bottom logbook-entry-header">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-initial"><?= esc($avatar) ?></div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fw-bold small text-body"><?= esc($log['author_name'] ?? 'User') ?></span>
                                            <span class="badge <?= $isTeamLog ? 'bg-light-success text-success border border-success-subtle' : 'bg-primary' ?>">
                                                <?= esc($displayRole) ?>
                                            </span>
                                            <span class="badge bg-secondary"><?= esc($log['status_name'] ?? '-') ?></span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <?= esc($displayDate) ?> &middot; <?= $isTeamLog ? 'Laporan Tim PIC' : 'Review Kepala Departemen' ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 logbook-entry-actions">
                                    <?php if ($isKadept) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 btn-beri-arahan"
                                            data-log-id="<?= (int) $log['id'] ?>"
                                            data-user-name="<?= esc($log['author_name'] ?? 'User', 'attr') ?>"
                                            data-log-date="<?= esc($displayDate, 'attr') ?>"
                                            data-blocker="<?= esc(strip_tags((string) ($log['blockers'] ?? '')), 'attr') ?>"
                                            data-notes="<?= esc(strip_tags((string) ($log['kadept_notes'] ?? '')), 'attr') ?>"
                                            title="<?= !empty($log['kadept_notes']) ? 'Ubah Arahan' : 'Beri Arahan' ?>">
                                            <i class="bi bi-chat-left-text"></i> <?= !empty($log['kadept_notes']) ? 'Ubah Arahan' : 'Beri Arahan' ?>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($canEditLog) : ?>
                                        <a href="<?= base_url('/projects/' . $project['id'] . '/logbooks/' . $log['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit Log">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-log"
                                            data-log-id="<?= (int) $log['id'] ?>"
                                            data-log-date="<?= esc($displayDate, 'attr') ?>"
                                            title="Hapus Log">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-3 logbook-entry-content">
                                <div>
                                    <div class="meta-item-label">Capaian Minggu Ini</div>
                                    <div class="logbook-richtext-content"><?= $log['achievements'] ?? '' ?></div>
                                </div>
                                <div>
                                    <div class="meta-item-label">Kendala & Masalah</div>
                                    <?php if (!empty(trim(strip_tags((string) ($log['blockers'] ?? ''))))): ?>
                                        <div class="logbook-callout-blocker"><?= $log['blockers'] ?></div>
                                    <?php else: ?>
                                        <div class="small text-muted ps-1">- Tidak ada kendala yang dilaporkan.</div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="meta-item-label">Rencana Minggu Depan</div>
                                    <div class="logbook-richtext-content"><?= $log['next_plans'] ?? '' ?></div>
                                </div>
                                <?php if (!empty(trim(strip_tags((string) ($log['kadept_notes'] ?? ''))))): ?>
                                    <div class="logbook-callout-kadept-wrapper">
                                        <div class="logbook-callout-kadept">
                                            <div class="meta-item-label text-primary mb-1">Arahan & Catatan Khusus Kadept</div>
                                            <div class="small text-body"><?= $log['kadept_notes'] ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 mt-3 border-top text-muted logbook-entry-footer" style="font-size: 0.75rem;">
                                <span>Status: <strong><?= esc($log['status_name'] ?? '-') ?></strong></span>
                                <span>Diperbarui: <?= esc($updatedDate) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>


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

<div class="modal fade" id="modalDeleteLogbook" tabindex="-1" aria-labelledby="modalDeleteLogbookLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold text-danger" id="modalDeleteLogbookLabel">Hapus Log Mingguan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Logbook tanggal <strong id="deleteLogbookDate">-</strong> akan dihapus permanen. Lanjutkan?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="formDeleteLogbook" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" data-cooldown="3">Ya, Hapus Log</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('#logbookFilterGroup .logbook-filter-btn');
        const feedItems = document.querySelectorAll('#logbookFeed .logbook-entry-card-wrapper');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');
                feedItems.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-type') === filter) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Delete Log Confirmation Handler
        const deleteLogbookModal = document.getElementById('modalDeleteLogbook');
        const deleteLogbookForm = document.getElementById('formDeleteLogbook');
        const deleteLogbookDate = document.getElementById('deleteLogbookDate');
        const deleteLogbookInstance = deleteLogbookModal && typeof bootstrap !== 'undefined'
            ? bootstrap.Modal.getOrCreateInstance(deleteLogbookModal)
            : null;

        document.querySelectorAll('.btn-delete-log').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const logId = this.getAttribute('data-log-id');
                const logDate = this.getAttribute('data-log-date') || '-';
                if (deleteLogbookForm && deleteLogbookInstance && logId) {
                    deleteLogbookForm.action = '<?= base_url('/projects/' . $project['id'] . '/logbooks') ?>/' + logId + '/delete';
                    deleteLogbookDate.textContent = logDate;
                    deleteLogbookInstance.show();
                }
            });
        });

        // Beri Arahan Modal Handler (Khusus Kepala Departemen)
        const modalBeriArahanEl = document.getElementById('modalBeriArahan');
        if (modalBeriArahanEl && typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modalBeriArahanEl);
            let activeTriggerBtn = null;

            document.querySelectorAll('.btn-beri-arahan').forEach(btn => {
                btn.addEventListener('click', function() {
                    activeTriggerBtn = this;
                    const logId = this.getAttribute('data-log-id');
                    const userName = this.getAttribute('data-user-name');
                    const logDate = this.getAttribute('data-log-date');
                    const blocker = this.getAttribute('data-blocker') || '';
                    const notes = this.getAttribute('data-notes') || '';

                    document.getElementById('modalLogId').value = logId;
                    document.getElementById('modalTargetUser').textContent = userName;
                    document.getElementById('modalTargetDate').textContent = logDate;
                    document.getElementById('modalKadeptNotes').value = notes;

                    const blockerContainer = document.getElementById('modalBlockerContainer');
                    const blockerText = document.getElementById('modalTargetBlocker');
                    if (blocker && blocker.trim() !== '' && blocker !== '-') {
                        blockerText.textContent = blocker;
                        blockerContainer.style.display = 'block';
                    } else {
                        blockerContainer.style.display = 'none';
                    }

                    bsModal.show();
                });
            });

            document.getElementById('btnSimpanArahan')?.addEventListener('click', function() {
                const notesInput = document.getElementById('modalKadeptNotes');
                const notesVal = notesInput.value.trim();
                if (!notesVal) {
                    notesInput.focus();
                    document.getElementById('modalArahanValidation').textContent = 'Arahan Kepala Departemen wajib diisi.';
                    document.getElementById('modalArahanValidation').classList.remove('d-none');
                    return;
                }

                const logId = document.getElementById('modalLogId').value;
                const reviewForm = document.getElementById('formBeriArahan');
                if (reviewForm && logId) {
                    reviewForm.action = '<?= base_url('/projects/' . $project['id'] . '/logbooks') ?>/' + logId + '/review';
                    reviewForm.submit();
                }
            });
        }

        // Smooth scroll to target anchor (e.g. #logbook-1) if present
        if (window.location.hash) {
            const targetElement = document.querySelector(window.location.hash);
            if (targetElement) {
                setTimeout(() => {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    targetElement.classList.add('highlight-pulse');
                    setTimeout(() => targetElement.classList.remove('highlight-pulse'), 2500);
                }, 300);
            }
        }
    });
</script>

<?php if ($isKadept) : ?>
    <!-- Modal Beri Arahan Kadept -->
    <div class="modal fade" id="modalBeriArahan" tabindex="-1" aria-labelledby="modalBeriArahanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold" id="modalBeriArahanLabel">Arahan & Catatan Kepala Departemen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="p-2 mb-3 rounded bg-light border" style="font-size: 0.85rem;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Pelapor: <strong class="text-body" id="modalTargetUser">-</strong></span>
                            <span class="text-muted" id="modalTargetDate">-</span>
                        </div>
                        <div id="modalBlockerContainer" style="display: none;">
                            <span class="text-danger fw-semibold">Kendala / Blocker Terlapor:</span>
                            <div class="small text-muted fst-italic mt-1 ps-2 border-start border-danger" id="modalTargetBlocker">-</div>
                        </div>
                    </div>

                    <form id="formBeriArahan" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" id="modalLogId" value="">
                        <div class="mb-3">
                            <label for="modalKadeptNotes" class="form-label fw-bold small">Instruksi & Arahan Tindak Lanjut <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="modalKadeptNotes" name="kadept_notes" rows="4" placeholder="Tuliskan instruksi koordinasi, saran teknis, atau tanggapan blocker bagi tim proyek..." required></textarea>
                            <div id="modalArahanValidation" class="text-danger small mt-2 d-none"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm btn-primary fw-semibold" id="btnSimpanArahan">
                        Simpan Arahan
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
