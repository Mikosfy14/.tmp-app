<?php
helper('deadline');
$projects = $projects ?? [];
$reportTitle = $reportTitle ?? 'Laporan Project Tracker';
$filterStatusLabel = $filterStatusLabel ?? 'Semua Status';
$filterPeriodLabel = $filterPeriodLabel ?? 'Semua Periode';
$userScopeLabel = $userScopeLabel ?? 'Semua Project Terkait';
$keyword = $keyword ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= esc($reportTitle ?? 'Laporan Project Tracker') ?></title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
            size: landscape;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #2b3035;
            line-height: 1.3;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #435ebe;
            padding-bottom: 8px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e1e2d;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-subtitle {
            font-size: 9px;
            color: #6c757d;
            margin: 0;
        }

        .meta-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 8.5px;
        }

        .meta-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-box td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .meta-label {
            color: #6c757d;
            font-weight: bold;
            width: 90px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .data-table th {
            background-color: #435ebe;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 5px;
            font-size: 8.5px;
            border: 1px solid #3b50a2;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 5px;
            border: 1px solid #dee2e6;
            vertical-align: top;
            font-size: 8.5px;
        }

        .data-table tr:nth-child(even) {
            background-color: #fcfcfd;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-align: center;
        }

        .badge-planning {
            background-color: #6c757d;
            color: #fff;
        }

        .badge-defining {
            background-color: #0dcaf0;
            color: #000;
        }

        .badge-designing {
            background-color: #0d6efd;
            color: #fff;
        }

        .badge-building {
            background-color: #ffc107;
            color: #000;
        }

        .badge-testing {
            background-color: #dc3545;
            color: #fff;
        }

        .badge-deployment {
            background-color: #198754;
            color: #fff;
        }

        .badge-completed {
            background-color: #198754;
            color: #fff;
        }

        .badge-overdue {
            background-color: #dc3545;
            color: #fff;
        }

        .badge-ontrack {
            background-color: #198754;
            color: #fff;
        }

        .badge-critical {
            background-color: #dc3545;
            color: #fff;
        }

        .badge-urgent {
            background-color: #fd7e14;
            color: #fff;
        }

        .badge-risk {
            background-color: #ffc107;
            color: #000;
        }

        .badge-pic {
            background-color: #eef2ff;
            color: #3b50a2;
            border: 1px solid #c7d2fe;
            margin-bottom: 2px;
            padding: 1px 4px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8px;
            color: #8c98a4;
            border-top: 1px solid #dee2e6;
            padding-top: 6px;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="report-title"><?= esc($reportTitle ?? 'Laporan Project Tracker') ?></div>
                <div class="report-subtitle">Sistem Manajemen & Tracking Portofolio Proyek</div>
            </td>
            <td class="text-right" style="width: 200px;">
                <div style="font-size: 8.5px; color: #6c757d;">Dicetak pada: <strong><?= date('d M Y, H:i') ?> WIB</strong></div>
                <div style="font-size: 8.5px; color: #6c757d;">Dicetak oleh: <strong><?= esc(session()->get('name') ?? 'User') ?></strong></div>
            </td>
        </tr>
    </table>

    <div class="meta-box">
        <table>
            <tr>
                <td class="meta-label">Status SDLC:</td>
                <td><?= esc($filterStatusLabel ?? 'Semua Status') ?></td>
                <td class="meta-label">Rentang Waktu:</td>
                <td><?= esc($filterPeriodLabel ?? 'Semua Periode') ?></td>
                <td class="meta-label">Total Data:</td>
                <td><strong><?= count($projects) ?> Project</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Pencarian:</td>
                <td><?= esc(!empty($keyword) ? $keyword : '-') ?></td>
                <td class="meta-label">Cakupan Akses:</td>
                <td><?= esc($userScopeLabel ?? 'Semua Project Terkait') ?></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 75px;">Kode</th>
                <th>Nama Project</th>
                <th style="width: 75px;">Status SDLC</th>
                <th style="width: 70px;">Deadline</th>
                <th style="width: 110px;">Assigned PIC</th>
                <th style="width: 65px;" class="text-center">Start</th>
                <th style="width: 65px;" class="text-center">End</th>
                <th style="width: 65px;" class="text-center">Promote</th>
                <th style="width: 110px;">Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($projects)) : ?>
                <?php $no = 1;
                foreach ($projects as $prj) : ?>
                    <?php
                    $status = $prj['status'] ?? 'Planning';
                    $statusSlug = strtolower(str_replace(' ', '', $status));
                    $isCompleted = is_project_completed($prj);
                    $deadline = get_deadline_status($prj['end_date'] ?? null, $isCompleted);
                    $deadlineSlug = strtolower(str_replace(' ', '', $deadline['label']));
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><strong><?= esc($prj['project_code'] ?? '-') ?></strong></td>
                        <td>
                            <strong style="color: #1e1e2d;"><?= esc($prj['name'] ?? '-') ?></strong>
                        </td>
                        <td>
                            <span class="badge badge-<?= $statusSlug ?>"><?= esc($status) ?></span>
                        </td>
                        <td>
                            <span class="badge badge-<?= $deadlineSlug ?>"><?= esc($deadline['label']) ?></span>
                        </td>
                        <td>
                            <?php if (!empty($prj['assigned_users'])) : ?>
                                <?php foreach ($prj['assigned_users'] as $u) : ?>
                                    <div class="badge badge-pic"><?= esc($u['name'] ?? '') ?></div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <span style="color: #adb5bd;">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= !empty($prj['start_date']) ? date('d/m/Y', strtotime($prj['start_date'])) : '-' ?></td>
                        <td class="text-center"><?= !empty($prj['end_date']) ? date('d/m/Y', strtotime($prj['end_date'])) : '-' ?></td>
                        <td class="text-center"><?= !empty($prj['promote_date']) ? date('d/m/Y', strtotime($prj['promote_date'])) : '-' ?></td>
                        <td style="font-size: 8px; color: #495057;"><?= !empty($prj['notes']) ? esc(mb_strimwidth($prj['notes'], 0, 90, '...')) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #6c757d;">Tidak ada data project yang sesuai dengan filter.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td>Dokumen ini digenerate secara otomatis oleh sistem.</td>
            <td class="text-right">Halaman 1</td>
        </tr>
    </table>
</body>

</html>