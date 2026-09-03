<?php
$applications = $applications ?? [];
$reportTitle = $reportTitle ?? 'Laporan Master Aplikasi Pengelolaan';
$filterCriticalityLabel = $filterCriticalityLabel ?? 'Semua Tingkat Criticality';
$keyword = $keyword ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= esc($reportTitle ?? 'Laporan Master Aplikasi') ?></title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
            size: landscape;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
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
            width: 100px;
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
            padding: 5px 4px;
            font-size: 8px;
            border: 1px solid #3b50a2;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 4px;
            border: 1px solid #dee2e6;
            vertical-align: top;
            font-size: 8px;
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
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-align: center;
        }

        .badge-crit {
            background-color: #dc3545;
            color: #fff;
        }

        .badge-high {
            background-color: #fd7e14;
            color: #fff;
        }

        .badge-med {
            background-color: #ffc107;
            color: #000;
        }

        .badge-low {
            background-color: #0dcaf0;
            color: #000;
        }

        .badge-pic {
            background-color: #eef2ff;
            color: #3b50a2;
            border: 1px solid #c7d2fe;
            padding: 1px 4px;
        }

        .badge-src-yes {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .badge-src-no {
            background-color: #f8d7da;
            color: #842029;
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
                <div class="report-title"><?= esc($reportTitle ?? 'Laporan Master Aplikasi') ?></div>
                <div class="report-subtitle">Sistem Manajemen Portofolio & Aset Aplikasi Departemen</div>
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
                <td class="meta-label">Criticality:</td>
                <td><?= esc($filterCriticalityLabel ?? 'Semua Tingkat Criticality') ?></td>
                <td class="meta-label">Pencarian:</td>
                <td><?= esc(!empty($keyword) ? $keyword : '-') ?></td>
                <td class="meta-label">Total Data:</td>
                <td><strong><?= count($applications) ?> Aplikasi</strong></td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px;" class="text-center">No</th>
                <th style="width: 110px;">Nama Aplikasi</th>
                <th style="width: 65px;">Criticality</th>
                <th style="width: 55px;">Tipe & Arch</th>
                <th style="width: 55px;">Platform</th>
                <th style="width: 65px;">Akses & Auth</th>
                <th style="width: 65px;">Dev & Deploy</th>
                <th style="width: 75px;">Business Owner</th>
                <th style="width: 75px;">System Owner</th>
                <th style="width: 80px;">Assigned PIC</th>
                <th style="width: 40px;" class="text-center">Source</th>
                <th>URL Production</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($applications)) : ?>
                <?php $no = 1;
                foreach ($applications as $app) : ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <strong style="color: #1e1e2d;"><?= esc($app['app_component'] ?? '-') ?></strong>
                            <?php if (!empty($app['description'])) : ?>
                                <div style="font-size: 7.5px; color: #6c757d; margin-top: 2px;"><?= esc(mb_strimwidth($app['description'], 0, 60, '...')) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge" style="background-color: #e2e8f0; color: #334155; border: 1px solid #cbd5e1;">
                                <?= esc($app['criticality_recovery'] ?? '-') ?>
                            </span>
                        </td>
                        <td>
                            <div><?= esc($app['app_type'] ?? '-') ?></div>
                            <div style="font-size: 7.5px; color: #6c757d;"><?= esc($app['arch_type'] ?? '-') ?></div>
                        </td>
                        <td><?= esc($app['platform'] ?? '-') ?></td>
                        <td>
                            <div><?= esc($app['access_type'] ?? '-') ?></div>
                            <div style="font-size: 7.5px; color: #6c757d;"><?= esc($app['login_auth'] ?? '-') ?></div>
                        </td>
                        <td>
                            <div><?= esc($app['development_type'] ?? '-') ?></div>
                            <div style="font-size: 7.5px; color: #6c757d;"><?= esc($app['deployment_type'] ?? '-') ?></div>
                        </td>
                        <td><?= esc($app['business_owner'] ?? '-') ?></td>
                        <td><?= esc($app['system_owner'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($app['assigned_user_name'])) : ?>
                                <span class="badge badge-pic"><?= esc($app['assigned_user_name']) ?></span>
                            <?php else : ?>
                                <span style="color: #adb5bd;">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ((int) ($app['has_source_code'] ?? 0) === 1) : ?>
                                <span class="badge badge-src-yes">Ada</span>
                            <?php else : ?>
                                <span class="badge badge-src-no">Tidak</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 7.5px; word-break: break-all;">
                            <?= !empty($app['url_prod']) ? esc($app['url_prod']) : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #6c757d;">Tidak ada data aplikasi yang sesuai dengan filter.</td>
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