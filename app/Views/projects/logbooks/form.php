<?php
/**
 * @var string $title
 * @var string $pageTitle
 * @var string $pageSubtitle
 * @var array $project
 * @var array $statusOptions
 * @var bool $isEdit
 * @var array $log
 * @var string $formAction
 */

$isKadept = (strtolower((string) session()->get('role_name')) === 'kepala departemen') || ((int) session()->get('role_id') === 1);
$currentType = old('log_type', $log['log_type'] ?? ($isKadept ? 'kadept_review' : 'team_log'));
$currentStatusId = (int) old('project_status_id', $log['project_status_id'] ?? ($project['project_status_id'] ?? 1));
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/vendors/quill/quill.snow.css') ?>">

<style>
    /* Quill Rich Text Styling & Collision Prevention */
    .quill-field-group {
        display: block;
        width: 100%;
        margin-bottom: 1.5rem;
    }

    .quill-field-group:last-child {
        margin-bottom: 0;
    }

    .quill-editor-wrapper {
        border: 1px solid #dfe3eb;
        border-radius: 6px;
        overflow: hidden;
        background-color: #ffffff;
    }

    .quill-editor-wrapper .ql-toolbar.ql-snow {
        border: none !important;
        border-bottom: 1px solid #dfe3eb !important;
        background-color: #f8f9fa;
        padding: 8px 10px;
    }

    .quill-editor-wrapper .ql-container.ql-snow {
        border: none !important;
        height: auto !important;
        font-family: inherit !important;
        font-size: 0.9rem;
    }

    .quill-editor-wrapper .ql-editor {
        min-height: 130px;
        height: auto !important;
        font-size: 0.9rem;
        line-height: 1.6;
        padding: 12px 14px;
    }

    /* Dark Mode Quill Adaptations */
    [data-bs-theme="dark"] .quill-editor-wrapper {
        border-color: #2b2b40 !important;
        background-color: #1e1e2d !important;
    }

    [data-bs-theme="dark"] .quill-editor-wrapper .ql-toolbar.ql-snow {
        background-color: #1b1b28 !important;
        border-bottom-color: #2b2b40 !important;
    }

    [data-bs-theme="dark"] .quill-editor-wrapper .ql-toolbar.ql-snow .ql-stroke {
        stroke: #c2c7d0 !important;
    }

    [data-bs-theme="dark"] .quill-editor-wrapper .ql-toolbar.ql-snow .ql-fill {
        fill: #c2c7d0 !important;
    }

    [data-bs-theme="dark"] .quill-editor-wrapper .ql-toolbar.ql-snow .ql-picker {
        color: #c2c7d0 !important;
    }

    [data-bs-theme="dark"] .quill-editor-wrapper .ql-container.ql-snow {
        background-color: #151521 !important;
        color: #e6eaee !important;
    }

    [data-bs-theme="dark"] .quill-editor-wrapper .ql-editor.ql-blank::before {
        color: #6c757d !important;
    }

    .project-form-card {
        max-width: 1120px;
        margin-inline: auto;
        overflow: visible;
        border: 1px solid #e8eaf1;
        box-shadow: 0 8px 28px rgba(31, 45, 61, .07) !important;
    }

    .project-form-page-header {
        max-width: 1120px;
        margin: 0 auto 1.25rem;
    }

    .project-form-section {
        padding: 1.5rem;
        border-bottom: 1px solid #edf0f5;
    }

    .project-form-section:last-of-type {
        border-bottom: 0;
    }

    .project-form-section-heading {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        margin-bottom: 1.25rem;
    }

    .project-form-section-icon {
        width: 34px;
        height: 34px;
        border-radius: 7px;
        font-size: .95rem;
        color: #435ebe;
        background: #eef1ff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .project-form-section-heading h5 {
        margin: 0 0 .15rem;
        font-size: 1rem;
        font-weight: 700;
    }

    .project-form-section-heading p {
        margin: 0;
        font-size: .875rem;
        color: #7c8193;
    }

    .project-form-card .form-label {
        margin-bottom: .45rem;
        color: #363b4e;
        font-size: .875rem;
        font-weight: 700;
    }

    .project-form-card .form-control,
    .project-form-card .form-select {
        min-height: 44px;
        border-color: #dfe3eb;
        border-radius: 6px;
    }

    .project-form-card textarea.form-control {
        min-height: 110px;
        resize: vertical;
    }

    .project-form-card .form-control:focus,
    .project-form-card .form-select:focus {
        border-color: #7185d5;
        box-shadow: 0 0 0 .2rem rgba(67, 94, 190, .12);
    }

    .project-form-actions {
        position: sticky;
        bottom: 0;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        background: #f8f9fc;
        border-top: 1px solid #e8eaf1;
        box-shadow: 0 -5px 16px rgba(31, 45, 61, .05);
    }

    .project-form-actions .btn {
        min-height: 42px;
        padding-inline: 1.25rem;
    }

    .context-pill-box {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 0.65rem 0.85rem;
    }

    [data-bs-theme="dark"] .project-form-card,
    [data-bs-theme="dark"] .project-form-actions,
    [data-bs-theme="dark"] .project-form-section {
        border-color: #2b2b40;
    }

    [data-bs-theme="dark"] .project-form-actions {
        background: #1b1b28;
        border-color: #2b2b40;
    }

    [data-bs-theme="dark"] .project-form-section-icon {
        background: rgba(67, 94, 190, .2);
        color: #8fa0f0;
    }

    [data-bs-theme="dark"] .project-form-card .form-label {
        color: #e6eaee;
    }

    [data-bs-theme="dark"] .project-form-section-heading p {
        color: #a0aec0;
    }

    [data-bs-theme="dark"] .context-pill-box {
        background-color: #1e1e2d;
        border-color: #2b2b40;
    }
</style>

<div class="page-heading project-form-page-header">
    <div class="mb-3">
        <a href="<?= base_url('/projects/detail/' . $project['id']) ?>" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Kembali ke Detail Proyek
        </a>
    </div>
    <h3 class="mb-1"><?= esc($pageTitle) ?></h3>
    <p class="text-subtitle text-muted mb-0"><?= esc($pageSubtitle) ?></p>
</div>

<div class="card project-form-card shadow-sm">
    <form id="formLogbook" action="<?= esc($formAction, 'attr') ?>" method="GET">
        <!-- Hidden input: Status SDLC otomatis mengikat snapshot status aktif proyek saat log disimpan -->
        <input type="hidden" id="project_status_id" name="project_status_id" value="<?= (int) $currentStatusId ?>">

        <!-- Section 1: Konteks Proyek -->
        <div class="project-form-section">
            <div class="project-form-section-heading">
                <div class="project-form-section-icon">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <div>
                    <h5>Konteks Proyek</h5>
                    <p>Informasi ringkas proyek yang sedang dievaluasi perkembangannya</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="context-pill-box">
                        <div class="meta-item-label mb-1">Kode Proyek</div>
                        <div class="fw-bold text-body font-monospace small"><?= esc($project['project_code'] ?? 'PRJ') ?></div>
                    </div>
                </div>
                <div class="col-12 col-md-5">
                    <div class="context-pill-box">
                        <div class="meta-item-label mb-1">Nama Proyek</div>
                        <div class="fw-bold text-body small text-truncate" title="<?= esc($project['name']) ?>"><?= esc($project['name']) ?></div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="context-pill-box">
                        <div class="meta-item-label mb-1">Status Proyek Saat Ini</div>
                        <div class="fw-bold text-body small"><?= esc($project['status_name'] ?? 'DEVELOPMENT') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Periode & Tipe Catatan -->
        <div class="project-form-section">
            <div class="project-form-section-heading">
                <div class="project-form-section-icon">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div>
                    <h5>Periode & Tipe Catatan</h5>
                    <p>Tipe catatan evaluasi mingguan dan tanggal pelaksanaan review</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="log_type" class="form-label">Tipe Catatan <span class="text-danger">*</span></label>
                    <select class="form-select" id="log_type" name="log_type" required>
                        <?php if ($isKadept) : ?>
                            <option value="kadept_review" <?= $currentType === 'kadept_review' ? 'selected' : '' ?>>Review Kepala Departemen</option>
                        <?php endif; ?>
                        <option value="team_log" <?= $currentType === 'team_log' ? 'selected' : '' ?>>Laporan Tim PIC</option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="log_date" class="form-label">Tanggal Review / Log <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="log_date" name="log_date" value="<?= esc($log['log_date'] ?? date('Y-m-d')) ?>" required>
                </div>
            </div>
        </div>

        <!-- Section 3: Rincian Capaian, Kendala, & Rencana Kerja -->
        <div class="project-form-section">
            <div class="project-form-section-heading">
                <div class="project-form-section-icon">
                    <i class="bi bi-card-text"></i>
                </div>
                <div>
                    <h5>Rincian Pengerjaan</h5>
                    <p>Capaian deliverable mingguan, identifikasi kendala teknis, dan target periode selanjutnya</p>
                </div>
            </div>

            <div class="d-flex flex-column">
                <!-- Capaian Minggu Ini -->
                <div class="quill-field-group">
                    <label class="form-label">Capaian Minggu Ini <span class="text-danger">*</span></label>
                    <div class="quill-editor-wrapper">
                        <div id="editorAchievements"><?= $log['achievements'] ?? '' ?></div>
                    </div>
                    <input type="hidden" name="achievements" id="achievements" value="<?= esc($log['achievements'] ?? '', 'attr') ?>">
                </div>

                <!-- Kendala & Masalah (Blockers) -->
                <div class="quill-field-group">
                    <label class="form-label">Kendala & Masalah (Blockers) <span class="text-muted fw-normal small">(Opsional)</span></label>
                    <div class="quill-editor-wrapper">
                        <div id="editorBlockers"><?= $log['blockers'] ?? '' ?></div>
                    </div>
                    <input type="hidden" name="blockers" id="blockers" value="<?= esc($log['blockers'] ?? '', 'attr') ?>">
                </div>

                <!-- Rencana Minggu Depan -->
                <div class="quill-field-group">
                    <label class="form-label">Rencana Minggu Depan <span class="text-danger">*</span></label>
                    <div class="quill-editor-wrapper">
                        <div id="editorNextPlans"><?= $log['next_plans'] ?? '' ?></div>
                    </div>
                    <input type="hidden" name="next_plans" id="next_plans" value="<?= esc($log['next_plans'] ?? '', 'attr') ?>">
                </div>
            </div>
        </div>

        <!-- Sticky Action Buttons Bar -->
        <div class="project-form-actions">
            <a href="<?= base_url('/projects/detail/' . $project['id']) ?>" class="btn btn-secondary">
                Batal
            </a>
            <button type="submit" class="btn btn-primary fw-semibold" id="btnSubmitLogbook">
                Simpan Log Mingguan
            </button>
        </div>

    </form>
</div>

<script src="<?= base_url('assets/vendors/quill/quill.min.js') ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formLogbook');

    // Inisialisasi Quill Editor dengan toolbar esensial
    const toolbarOptions = [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['clean']
    ];

    const quillAchievements = new Quill('#editorAchievements', {
        theme: 'snow',
        placeholder: 'Tuliskan capaian mingguan dengan format poin atau paragraf...',
        modules: { toolbar: toolbarOptions }
    });

    const quillBlockers = new Quill('#editorBlockers', {
        theme: 'snow',
        placeholder: 'Tuliskan kendala teknis, dependensi eksternal, atau approvals jika ada. Kosongkan jika lancar...',
        modules: { toolbar: toolbarOptions }
    });

    const quillNextPlans = new Quill('#editorNextPlans', {
        theme: 'snow',
        placeholder: 'Tuliskan fokus pekerjaan dan target penyelesaian minggu depan...',
        modules: { toolbar: toolbarOptions }
    });

    // Proteksi penekanan tombol Enter pada input teks & angka agar tidak trigger submit tidak sengaja
    form.querySelectorAll('input:not([type="submit"]):not([type="button"])').forEach(input => {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    });

    // Sinkronisasi data Quill ke hidden input saat submit
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const isAchievementsEmpty = quillAchievements.getText().trim().length === 0;
        const isBlockersEmpty = quillBlockers.getText().trim().length === 0;
        const isNextPlansEmpty = quillNextPlans.getText().trim().length === 0;

        document.getElementById('achievements').value = isAchievementsEmpty ? '' : quillAchievements.root.innerHTML;
        document.getElementById('blockers').value = isBlockersEmpty ? '' : quillBlockers.root.innerHTML;
        document.getElementById('next_plans').value = isNextPlansEmpty ? '' : quillNextPlans.root.innerHTML;

        if (isAchievementsEmpty) {
            quillAchievements.focus();
            if (typeof Swal !== 'undefined') {
                Swal.fire('Validasi Gagal', 'Kolom Capaian Minggu Ini wajib diisi.', 'warning');
            } else {
                alert('Kolom Capaian Minggu Ini wajib diisi.');
            }
            return;
        }

        if (isNextPlansEmpty) {
            quillNextPlans.focus();
            if (typeof Swal !== 'undefined') {
                Swal.fire('Validasi Gagal', 'Kolom Rencana Minggu Depan wajib diisi.', 'warning');
            } else {
                alert('Kolom Rencana Minggu Depan wajib diisi.');
            }
            return;
        }

        const submitBtn = document.getElementById('btnSubmitLogbook');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        setTimeout(function () {
            window.location.href = '<?= base_url('/projects/detail/' . $project['id']) ?>';
        }, 600);
    });
});
</script>

<?= $this->endSection() ?>
