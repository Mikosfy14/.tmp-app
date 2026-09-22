<?php
helper(['deadline', 'notification']);
$profileName = trim((string) session()->get('name'));
$profileInitial = strtoupper(substr($profileName !== '' ? $profileName : 'U', 0, 1));
$deadlineNotifications = get_user_deadline_notifications();
$feedbackNotifications = get_user_feedback_notifications();
$totalNotifications = count($deadlineNotifications) + count($feedbackNotifications);
?>

<style>
    .navbar-top {
        position: relative;
        z-index: 1030;
    }

    .navbar-action-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        padding: 0;
        border-radius: 50%;
        transition: all .2s ease;
        position: relative;
    }

    .navbar-profile-trigger {
        color: #fff;
        background: #435ebe;
        border: 2px solid #fff;
        box-shadow: 0 2px 10px rgba(31, 45, 61, .18);
        font-weight: 800;
    }

    .navbar-profile-trigger:hover,
    .navbar-profile-trigger:focus,
    .navbar-profile-trigger[aria-expanded="true"] {
        color: #fff;
        background: #364da3;
        box-shadow: 0 0 0 .2rem rgba(67, 94, 190, .18);
    }

    .navbar-bell-trigger {
        color: #607080;
        background: #f2f4f8;
        border: 0;
    }

    .navbar-bell-trigger i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        line-height: 1;
    }

    .navbar-bell-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        transform: translate(20%, -20%);
        font-size: 0.65rem;
        padding: 0.25em 0.5em;
        line-height: 1;
    }

    .navbar-bell-trigger:hover,
    .navbar-bell-trigger:focus,
    .navbar-bell-trigger[aria-expanded="true"] {
        color: #435ebe;
        background: #eef1ff;
    }

    .navbar-profile-menu {
        min-width: 220px;
        z-index: 1080;
    }

    .navbar-notification-menu {
        width: 360px;
        max-height: 480px;
        overflow-y: auto;
        z-index: 1080;
    }

    @media (max-width: 575.98px) {
        .navbar-notification-menu {
            position: fixed !important;
            top: 4rem !important;
            right: 0.5rem !important;
            left: 0.5rem !important;
            width: auto;
            max-width: none;
            transform: none !important;
        }

        .navbar-notification-menu .dropdown-item {
            min-width: 0;
        }
    }

    .notif-tab-nav .nav-link {
        color: #607080;
        border-radius: 6px;
        font-size: 0.8rem;
    }

    .notif-tab-nav .nav-link.active {
        background-color: #435ebe;
        color: #fff;
    }

    .notif-comment-item {
        transition: background-color 0.15s ease;
        text-decoration: none;
    }

    .notif-comment-item:hover {
        background-color: #f8f9fa;
    }

    [data-bs-theme="dark"] .navbar-profile-trigger {
        border-color: #252539;
    }

    [data-bs-theme="dark"] .navbar-bell-trigger {
        color: #a6a8b8;
        background: #252539;
    }

    [data-bs-theme="dark"] .navbar-bell-trigger:hover,
    [data-bs-theme="dark"] .navbar-bell-trigger:focus,
    [data-bs-theme="dark"] .navbar-bell-trigger[aria-expanded="true"] {
        color: #fff;
        background: #31314d;
    }

    [data-bs-theme="dark"] .notif-tab-nav .nav-link {
        color: #a6a8b8;
    }

    [data-bs-theme="dark"] .notif-tab-nav .nav-link.active {
        background-color: #435ebe;
        color: #fff;
    }

    [data-bs-theme="dark"] .navbar-notification-menu .bg-light {
        background-color: #1b1b29 !important;
    }

    [data-bs-theme="dark"] .notif-comment-item:hover {
        background-color: #232336 !important;
    }

    [data-bs-theme="dark"] .navbar-notification-menu .border-bottom {
        border-color: #2b2b40 !important;
    }

    [data-bs-theme="dark"] .navbar-profile-menu .dropdown-header small {
        color: #a6a8b8 !important;
    }
</style>

<header>
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="ms-auto d-flex align-items-center gap-2">
                    <!-- Notification Bell Dropdown (2-Tab Model) -->
                    <div class="dropdown">
                        <button id="notificationDropdown" class="navbar-action-trigger navbar-bell-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi & Diskusi" title="Pusat Notifikasi">
                            <i class="bi bi-bell-fill" aria-hidden="true"></i>
                            <?php if ($totalNotifications > 0): ?>
                                <span class="navbar-bell-badge badge rounded-pill bg-danger border border-light">
                                    <?= $totalNotifications > 99 ? '99+' : $totalNotifications ?>
                                    <span class="visually-hidden">notifikasi belum dibaca</span>
                                </span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end navbar-notification-menu shadow-sm p-0" aria-labelledby="notificationDropdown">
                            <!-- Header Tabs Navigation -->
                            <div class="p-2 border-bottom bg-light">
                                <ul class="nav nav-pills nav-fill notif-tab-nav" id="notifTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active py-1 px-2 fw-semibold" id="tab-deadline-btn" data-bs-toggle="pill" data-bs-target="#notif-deadline-pane" type="button" role="tab" aria-controls="notif-deadline-pane" aria-selected="true" onclick="event.stopPropagation();">
                                            <i class="bi bi-alarm me-1"></i>Deadline
                                            <?php if (!empty($deadlineNotifications)): ?>
                                                <span class="badge bg-danger ms-1"><?= count($deadlineNotifications) ?></span>
                                            <?php endif; ?>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1 px-2 fw-semibold" id="tab-feedback-btn" data-bs-toggle="pill" data-bs-target="#notif-feedback-pane" type="button" role="tab" aria-controls="notif-feedback-pane" aria-selected="false" onclick="event.stopPropagation();">
                                            <i class="bi bi-chat-dots me-1"></i>Diskusi
                                            <?php if (!empty($feedbackNotifications)): ?>
                                                <span class="badge bg-primary ms-1"><?= count($feedbackNotifications) ?></span>
                                            <?php endif; ?>
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <!-- Tab Contents -->
                            <div class="tab-content" id="notifTabContent">
                                <!-- Tab 1: Peringatan Deadline -->
                                <div class="tab-pane fade show active" id="notif-deadline-pane" role="tabpanel" aria-labelledby="tab-deadline-btn">
                                    <?php if (!empty($deadlineNotifications)): ?>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($deadlineNotifications as $notif): ?>
                                                <?php
                                                $daysLeft = $notif['days_left'] ?? null;
                                                $notifRelativeText = null;
                                                $notifRelativeClass = 'text-muted';
                                                if ($daysLeft !== null) {
                                                    if ($daysLeft < 0) {
                                                        $notifRelativeText = abs($daysLeft) . ' hari terlambat';
                                                        $notifRelativeClass = 'text-danger fw-semibold';
                                                    } elseif ($daysLeft === 0) {
                                                        $notifRelativeText = 'Tenggat hari ini';
                                                        $notifRelativeClass = 'text-danger fw-bold';
                                                    } else {
                                                        $notifRelativeText = 'Sisa ' . $daysLeft . ' hari';
                                                        $notifRelativeClass = !empty($notif['deadline_class']) ? 'text-' . esc($notif['deadline_class']) . ' fw-semibold' : 'text-muted';
                                                    }
                                                }
                                                ?>
                                                <li class="border-bottom">
                                                    <a class="dropdown-item p-3 text-wrap" href="<?= base_url('/projects/detail/' . $notif['id']) ?>">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <strong class="text-dark small text-truncate" style="max-width: 200px;" title="<?= esc($notif['name']) ?>"><?= esc($notif['name']) ?></strong>
                                                            <span class="badge bg-light-<?= esc($notif['deadline_class']) ?> text-<?= esc($notif['deadline_class']) ?> ms-2"><?= esc($notif['deadline_label']) ?></span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center text-muted small">
                                                            <span><i class="bi bi-tag me-1"></i><?= esc($notif['project_code']) ?></span>
                                                            <span><i class="bi bi-calendar-event me-1"></i><?= !empty($notif['end_date']) ? date('d M Y', strtotime($notif['end_date'])) : '-' ?></span>
                                                        </div>
                                                        <?php if ($notifRelativeText !== null): ?>
                                                            <div class="mt-1 small <?= $notifRelativeClass ?>">
                                                                <i class="bi bi-clock-history me-1"></i><?= esc($notifRelativeText) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="p-4 text-center text-muted small">
                                            <i class="bi bi-check2-circle fs-4 d-block mb-1 text-success"></i>
                                            Semua deadline proyek terpantau aman
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tab 2: Diskusi & Feedback Logbook -->
                                <div class="tab-pane fade" id="notif-feedback-pane" role="tabpanel" aria-labelledby="tab-feedback-btn">
                                    <?php if (!empty($feedbackNotifications)): ?>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($feedbackNotifications as $fb): ?>
                                                <?php
                                                $senderName = $fb['sender_name'] ?? $fb['commenter_name'] ?? 'User';
                                                $senderRole = $fb['sender_role'] ?? $fb['commenter_role'] ?? 'PIC';
                                                $fbInitials = strtoupper(substr($senderName, 0, 2));
                                                $timeStr = !empty($fb['created_at']) ? date('d M, H:i', strtotime($fb['created_at'])) : '';
                                                $msgPreview = strip_tags($fb['message'] ?? '');
                                                if (mb_strlen($msgPreview) > 75) {
                                                    $msgPreview = mb_substr($msgPreview, 0, 72) . '...';
                                                }
                                                $detailUrl = base_url('/projects/detail/' . ($fb['project_id'] ?? 1) . (!empty($fb['logbook_id']) ? '#logbook-' . $fb['logbook_id'] : ''));
                                                ?>
                                                <li class="border-bottom">
                                                    <a class="dropdown-item p-3 text-wrap notif-comment-item" href="<?= $detailUrl ?>">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 0.7rem;"><?= esc($fbInitials) ?></span>
                                                            <strong class="text-dark small text-truncate" style="max-width: 170px;" title="<?= esc($senderName) ?>">
                                                                <?= esc($senderName) ?>
                                                            </strong>
                                                            <span class="badge bg-light text-muted border ms-auto" style="font-size: 0.65rem;"><?= esc($senderRole) ?></span>
                                                        </div>
                                                        <div class="text-body small mb-1" style="font-size: 0.8rem; line-height: 1.35;">
                                                            <?= esc($msgPreview) ?>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.7rem;">
                                                            <span class="text-truncate" style="max-width: 220px;" title="<?= esc($fb['project_name'] ?? 'Project') ?>">
                                                                <i class="bi bi-folder2-open me-1"></i><?= esc($fb['project_name'] ?? 'Project') ?>
                                                            </span>
                                                            <span><i class="bi bi-clock me-1"></i><?= esc($timeStr) ?></span>
                                                        </div>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="p-4 text-center text-muted small">
                                            Tidak ada tanggapan atau feedback baru
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="user-menu dropdown ms-1">
                        <button id="profileDropdown" class="navbar-action-trigger navbar-profile-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka menu profil" title="Profil Saya">
                            <?= esc($profileInitial) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end navbar-profile-menu shadow-sm p-0" aria-labelledby="profileDropdown">
                            <li>
                                <div class="dropdown-header">
                                    <div class="fw-bold text-body"><?= esc($profileName ?: 'User') ?></div>
                                    <small><?= esc(session()->get('role_name')) ?></small>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?= base_url('/profile') ?>"><i class="bi bi-person-vcard me-2"></i>Profil Saya</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="<?= base_url('/logout') ?>"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>