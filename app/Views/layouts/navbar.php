<?php
helper('deadline');
$profileName = trim((string) session()->get('name'));
$profileInitial = strtoupper(substr($profileName !== '' ? $profileName : 'U', 0, 1));
$deadlineNotifications = get_user_deadline_notifications();
?>

<style>
    .navbar-top {
        position: relative;
        z-index: 1030;
    }

    .navbar-profile-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        padding: 0;
        color: #fff;
        background: #435ebe;
        border: 2px solid #fff;
        border-radius: 50%;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        padding: 0;
        color: #607080;
        background: #f2f4f8;
        border: 0;
        border-radius: 50%;
        transition: all .2s ease;
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
        width: 330px;
        max-height: 420px;
        overflow-y: auto;
        z-index: 1080;
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
</style>

<header>
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="ms-auto d-flex align-items-center gap-2">
                    <!-- Notification Bell Dropdown -->
                    <div class="dropdown">
                        <button id="notificationDropdown" class="navbar-bell-trigger position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi Deadline Project" title="Notifikasi Deadline">
                            <i class="bi bi-bell-fill fs-5"></i>
                            <?php if (!empty($deadlineNotifications)): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem; padding: 0.25em 0.55em;">
                                    <?= count($deadlineNotifications) > 99 ? '99+' : count($deadlineNotifications) ?>
                                    <span class="visually-hidden">notifikasi deadline</span>
                                </span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end navbar-notification-menu shadow-sm p-0" aria-labelledby="notificationDropdown">
                            <li class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                                <span class="fw-bold fs-6 mb-0 text-dark"><i class="bi bi-bell me-2 text-primary"></i>Deadline Alert</span>
                                <?php if (!empty($deadlineNotifications)): ?>
                                    <span class="badge bg-danger"><?= count($deadlineNotifications) ?> Butuh Tindakan</span>
                                <?php endif; ?>
                            </li>
                            <?php if (!empty($deadlineNotifications)): ?>
                                <?php foreach ($deadlineNotifications as $notif): ?>
                                    <li>
                                        <a class="dropdown-item p-3 border-bottom text-wrap" href="<?= base_url('/projects/detail/' . $notif['id']) ?>">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong class="text-dark small text-truncate" style="max-width: 190px;" title="<?= esc($notif['name']) ?>"><?= esc($notif['name']) ?></strong>
                                                <span class="badge bg-light-<?= esc($notif['deadline_class']) ?> text-<?= esc($notif['deadline_class']) ?> ms-2"><?= esc($notif['deadline_label']) ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                                <span><i class="bi bi-tag me-1"></i><?= esc($notif['project_code']) ?></span>
                                                <span><i class="bi bi-calendar-event me-1"></i><?= !empty($notif['end_date']) ? date('d M Y', strtotime($notif['end_date'])) : '-' ?></span>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="p-4 text-center text-muted small">
                                    <i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>
                                    Semua deadline project berjalan aman (On Track).
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="user-menu dropdown ms-1">
                        <button id="profileDropdown" class="navbar-profile-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka menu profil" title="Profil Saya">
                            <?= esc($profileInitial) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end navbar-profile-menu shadow-sm" aria-labelledby="profileDropdown">
                            <li>
                                <div class="dropdown-header">
                                    <div class="fw-bold text-body"><?= esc($profileName ?: 'User') ?></div>
                                    <small><?= esc(session()->get('role_name')) ?></small>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= base_url('/profile') ?>"><i class="bi bi-person-vcard me-2"></i>Profil Saya</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('/profile/edit') ?>"><i class="bi bi-pencil-square me-2"></i>Edit Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= base_url('/logout') ?>"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
