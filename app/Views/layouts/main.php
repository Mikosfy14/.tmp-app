<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);

            // Apply the saved sidebar state before the page is painted.
            if (localStorage.getItem('sidebar-state') === 'closed') {
                document.documentElement.classList.add('sidebar-closed');
            }
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon / Title Bar Icon -->
    <link rel="shortcut icon" href="<?= base_url('assets/images/logo/tmp_logo.png') ?>" type="image/png">
    <link rel="icon" href="<?= base_url('assets/images/logo/tmp_logo.png') ?>" type="image/png">
    <title><?= esc($title ?? 'Dashboard') ?> - .tmp Project Manager</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?= $this->renderSection('styles') ?>

    <style>
        body,
        #sidebar,
        .sidebar-wrapper,
        .sidebar-link,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        span,
        p,
        a {
            font-family: 'Nunito', sans-serif !important;
        }

        .sidebar-link {
            display: flex !important;
            align-items: center !important;
        }

        .sidebar-link i {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-right: 0.75rem !important;
        }

        .sidebar-wrapper .sidebar-header .logo img {
            height: 62px !important;
            width: auto !important;
            max-width: 400px !important;
            object-fit: contain !important;
        }

        #sidebar {
            position: relative;
            z-index: 1040;
        }

        #sidebar .sidebar-wrapper {
            display: flex;
            flex-direction: column;
            left: 0 !important;
            z-index: 1041;
            box-shadow: 0 0 1.5rem rgba(20, 24, 40, .12);
            transition: transform .25s ease-out;
            height: 100vh;
            height: 100dvh;
        }

        #sidebar .sidebar-wrapper .sidebar-header {
            padding: 1.5rem 1.5rem 1rem;
            flex-shrink: 0;
        }

        #sidebar .sidebar-wrapper .sidebar-header .logo img {
            height: 52px !important;
            width: auto !important;
            max-width: 175px !important;
            object-fit: contain !important;
        }

        .sidebar-wrapper .sidebar-menu {
            flex: 1 1 auto;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding-bottom: 0.5rem;
        }

        .sidebar-wrapper .menu {
            padding: 0 1.25rem !important;
            margin-top: 0.5rem !important;
        }

        .sidebar-wrapper .menu .sidebar-title {
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.075em !important;
            color: #8fa0b5 !important;
            padding: 0.75rem 0.75rem 0.35rem !important;
            margin: 0.75rem 0 0.25rem !important;
        }

        .sidebar-wrapper .menu .sidebar-item {
            margin-top: 0.25rem !important;
        }

        .sidebar-wrapper .menu .sidebar-item .sidebar-link {
            border-radius: 8px !important;
            padding: 0.65rem 0.85rem !important;
            font-size: 0.92rem !important;
            font-weight: 600 !important;
            color: #495057 !important;
            transition: all 0.2s ease !important;
        }

        .sidebar-wrapper .menu .sidebar-item .sidebar-link:hover {
            background-color: #f1f4fb !important;
            color: #435ebe !important;
            transform: translateX(2px);
        }

        .sidebar-wrapper .menu .sidebar-item.active .sidebar-link {
            background-color: #435ebe !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(67, 94, 190, 0.28) !important;
        }

        .sidebar-wrapper .menu .sidebar-item.active .sidebar-link i,
        .sidebar-wrapper .menu .sidebar-item.active .sidebar-link svg {
            color: #ffffff !important;
            fill: #ffffff !important;
        }

        /* Sidebar User Footer */
        .sidebar-footer {
            flex-shrink: 0;
            padding: 0.85rem 1.25rem;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            background: rgba(248, 249, 250, 0.65);
        }

        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .btn-sidebar-action {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #607080;
            background: transparent;
            border: 0;
            text-decoration: none;
            transition: all .2s ease;
        }

        .btn-sidebar-action:hover {
            background: rgba(0, 0, 0, 0.06);
            color: #435ebe;
        }

        .btn-sidebar-action.text-danger:hover {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545 !important;
        }

        #sidebar:not(.active) .sidebar-wrapper,
        html.sidebar-closed #sidebar .sidebar-wrapper {
            transform: translateX(-100%);
        }

        html.sidebar-closed #sidebar-backdrop,
        html.sidebar-closed #sidebar.active #sidebar-backdrop {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transition: none !important;
        }

        #sidebar-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1040;
            background: rgba(15, 18, 30, .48);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .25s ease, visibility .25s ease;
        }

        #sidebar.active #sidebar-backdrop {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        #main {
            margin-left: 0 !important;
        }

        /* Mobile Scoped Sidebar Pinning & Scroll Lock (< 1200px) */
        @media (max-width: 1199.98px) {
            #sidebar .sidebar-wrapper {
                position: fixed !important;
                top: 0 !important;
                bottom: 0 !important;
                left: 0 !important;
                height: 100vh !important;
                height: 100dvh !important;
                max-height: 100dvh !important;
                width: min(265px, 72vw) !important;
                overflow-y: auto !important;
                overscroll-behavior: contain !important;
                -webkit-overflow-scrolling: touch !important;
                z-index: 1050 !important;
                touch-action: pan-y !important;
            }

            #sidebar .sidebar-wrapper .sidebar-header {
                padding: 1.25rem 1.25rem 1rem !important;
            }

            body.sidebar-open {
                overflow: hidden !important;
                touch-action: none !important;
            }

            #sidebar-backdrop {
                z-index: 1045 !important;
                position: fixed !important;
                inset: 0 !important;
                background: rgba(15, 18, 30, .55) !important;
                backdrop-filter: blur(2px);
                -webkit-backdrop-filter: blur(2px);
            }
        }

        [data-bs-theme="dark"] body {
            background-color: #151521 !important;
            color: #a6a8b8 !important;
        }

        [data-bs-theme="dark"] #main,
        [data-bs-theme="dark"] #main-content {
            background-color: #151521 !important;
            color: #a6a8b8 !important;
        }

        [data-bs-theme="dark"] .sidebar-wrapper {
            background-color: #1e1e2d !important;
            border-right: 1px solid #2b2b40 !important;
        }

        [data-bs-theme="dark"] .sidebar-wrapper .menu .sidebar-link {
            color: #a6a8b8 !important;
        }

        [data-bs-theme="dark"] .sidebar-wrapper .menu .sidebar-link:hover {
            background-color: #2b2b40 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .sidebar-wrapper .menu .sidebar-item.active .sidebar-link {
            background-color: #435ebe !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(67, 94, 190, 0.4) !important;
        }

        [data-bs-theme="dark"] .sidebar-wrapper .menu .sidebar-title {
            color: #636682 !important;
        }

        [data-bs-theme="dark"] .sidebar-footer {
            border-color: #2b2b40;
            background: rgba(21, 21, 33, 0.5);
        }

        [data-bs-theme="dark"] .btn-sidebar-action {
            color: #a6a8b8;
        }

        [data-bs-theme="dark"] .btn-sidebar-action:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        [data-bs-theme="dark"] .btn-sidebar-action.text-danger:hover {
            background: rgba(243, 97, 109, 0.18);
            color: #ff6b7d !important;
        }

        [data-bs-theme="dark"] .card {
            background-color: #1e1e2d !important;
            color: #ffffff !important;
            border: 1px solid #2b2b40 !important;
        }

        [data-bs-theme="dark"] .card-header,
        [data-bs-theme="dark"] .card-footer,
        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            background-color: #1e1e2d !important;
            border-color: #2b2b40 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .modal-content,
        [data-bs-theme="dark"] .dropdown-menu,
        [data-bs-theme="dark"] .list-group-item {
            background-color: #1e1e2d !important;
            border-color: #2b2b40 !important;
            color: #f5f7ff !important;
        }

        [data-bs-theme="dark"] .table {
            --bs-table-bg: #1e1e2d;
            --bs-table-color: #f5f7ff;
            --bs-table-border-color: #2b2b40;
            --bs-table-hover-bg: #2b2b40;
            --bs-table-hover-color: #ffffff;
            color: #f5f7ff !important;
        }

        [data-bs-theme="dark"] .table-light,
        [data-bs-theme="dark"] .table-light>tr,
        [data-bs-theme="dark"] .table-light>tr>th,
        [data-bs-theme="dark"] .table-light>tr>td,
        [data-bs-theme="dark"] .table thead,
        [data-bs-theme="dark"] .table thead th {
            background-color: #252539 !important;
            color: #ffffff !important;
            border-color: #2b2b40 !important;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .input-group-text {
            background-color: #151521 !important;
            border-color: #2b2b40 !important;
            color: #f5f7ff !important;
        }

        [data-bs-theme="dark"] .form-control::placeholder,
        [data-bs-theme="dark"] textarea::placeholder {
            color: #6f7288 !important;
        }

        [data-bs-theme="dark"] .text-dark,
        [data-bs-theme="dark"] .text-black,
        [data-bs-theme="dark"] .text-gray-600,
        [data-bs-theme="dark"] .text-body,
        [data-bs-theme="dark"] h1,
        [data-bs-theme="dark"] h2,
        [data-bs-theme="dark"] h3,
        [data-bs-theme="dark"] h4,
        [data-bs-theme="dark"] h5,
        [data-bs-theme="dark"] h6 {
            color: #f5f7ff !important;
        }

        [data-bs-theme="dark"] .text-muted,
        [data-bs-theme="dark"] .text-secondary {
            color: #a6a8b8 !important;
        }

        [data-bs-theme="dark"] .text-primary {
            color: #8fa0f0 !important;
        }

        [data-bs-theme="dark"] .text-success {
            color: #4cd98b !important;
        }

        [data-bs-theme="dark"] .text-danger {
            color: #ff6b7d !important;
        }

        [data-bs-theme="dark"] .text-warning {
            color: #ffd166 !important;
        }

        [data-bs-theme="dark"] .text-info {
            color: #4dd8f7 !important;
        }

        [data-bs-theme="dark"] .bg-light,
        [data-bs-theme="dark"] .bg-white {
            background-color: #252539 !important;
            color: #f5f7ff !important;
        }

        [data-bs-theme="dark"] .bg-light-primary {
            background-color: rgba(67, 94, 190, 0.22) !important;
        }

        [data-bs-theme="dark"] .bg-light-secondary {
            background-color: rgba(108, 117, 125, 0.22) !important;
        }

        [data-bs-theme="dark"] .bg-light-success {
            background-color: rgba(25, 135, 84, 0.22) !important;
        }

        [data-bs-theme="dark"] .bg-light-danger {
            background-color: rgba(220, 53, 69, 0.22) !important;
        }

        [data-bs-theme="dark"] .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.22) !important;
        }

        [data-bs-theme="dark"] .bg-light-info {
            background-color: rgba(13, 202, 240, 0.22) !important;
        }

        [data-bs-theme="dark"] .border-primary-subtle {
            border-color: rgba(67, 94, 190, 0.35) !important;
        }

        [data-bs-theme="dark"] .border-success-subtle {
            border-color: rgba(25, 135, 84, 0.35) !important;
        }

        [data-bs-theme="dark"] .border-danger-subtle {
            border-color: rgba(220, 53, 69, 0.35) !important;
        }

        [data-bs-theme="dark"] .border-warning-subtle {
            border-color: rgba(255, 193, 7, 0.35) !important;
        }

        [data-bs-theme="dark"] .border-info-subtle {
            border-color: rgba(13, 202, 240, 0.35) !important;
        }

        [data-bs-theme="dark"] .border-secondary-subtle {
            border-color: rgba(108, 117, 125, 0.35) !important;
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: #d6d8ea !important;
        }

        [data-bs-theme="dark"] .dropdown-item:hover,
        [data-bs-theme="dark"] .dropdown-item:focus {
            background-color: #2b2b40 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .dropdown-item.active,
        [data-bs-theme="dark"] .dropdown-item:active {
            background-color: #435ebe !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .dropdown-item.text-danger {
            color: #ff6b7d !important;
        }

        [data-bs-theme="dark"] .dropdown-item.text-danger:hover,
        [data-bs-theme="dark"] .dropdown-item.text-danger:focus {
            background-color: rgba(220, 53, 69, 0.18) !important;
            color: #ff8595 !important;
        }

        [data-bs-theme="dark"] .dropdown-header {
            color: #8fa0b5 !important;
        }

        [data-bs-theme="dark"] .dropdown-divider {
            border-color: #2b2b40 !important;
            opacity: 1 !important;
        }

        [data-bs-theme="dark"] .alert-light {
            background-color: #252539 !important;
            border-color: #2b2b40 !important;
            color: #f5f7ff !important;
        }

        [data-bs-theme="dark"] .alert-light-primary {
            background-color: rgba(67, 94, 190, 0.16) !important;
            border-color: rgba(67, 94, 190, 0.35) !important;
            color: #f5f7ff !important;
        }

        [data-bs-theme="dark"] .burger-btn i {
            color: #a6a8b8 !important;
        }

        [data-bs-theme="dark"] .burger-btn:hover i {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .btn-close:not(.btn-close-white) {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        [data-bs-theme="dark"] .btn-light {
            background-color: #252539 !important;
            border-color: #2b2b40 !important;
            color: #f5f7ff !important;
        }

        [data-bs-theme="dark"] .btn-light:hover {
            background-color: #31314d !important;
            border-color: #3b3b55 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .form-label {
            color: #e6eaee !important;
        }

        [data-bs-theme="dark"] .form-control:disabled,
        [data-bs-theme="dark"] .form-control[readonly],
        [data-bs-theme="dark"] .form-select:disabled {
            background-color: #1c1c2b !important;
            color: #8a8d9e !important;
        }

        [data-bs-theme="dark"] .pagination .page-item .page-link {
            color: #a6a8b8;
            background-color: #1e1e2d;
            border-color: #2b2b40;
        }

        [data-bs-theme="dark"] .pagination .page-item:not(.active) .page-link:hover {
            background-color: #2b2b40;
            color: #ffffff;
            border-color: #3b3b55;
        }

        [data-bs-theme="dark"] .pagination .page-item.active .page-link {
            background-color: #435ebe !important;
            border-color: #435ebe !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .pagination .page-item.disabled .page-link {
            color: #607080 !important;
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: #2b2b40 !important;
        }

        [data-bs-theme="dark"] .border,
        [data-bs-theme="dark"] .border-top,
        [data-bs-theme="dark"] .border-bottom,
        [data-bs-theme="dark"] .border-light {
            border-color: #2b2b40 !important;
        }

        [data-bs-theme="dark"] .navbar-fixed {
            background-color: #1e1e2d !important;
        }

        [data-bs-theme="dark"] .navbar-top {
            background-color: #151521 !important;
        }

        body.session-expired-active .modal-backdrop {
            background-color: rgba(15, 18, 30, .48) !important;
            opacity: 1 !important;
            transition: opacity .25s ease !important;
        }

        #modalSessionExpired .modal-content {
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        [data-bs-theme="dark"] #modalSessionExpired .modal-content {
            background-color: #1e1e2d !important;
            color: #ffffff !important;
            border-color: #2b2b40 !important;
        }
    </style>
</head>

<body>
    <div id="app">
        <?= $this->include('layouts/sidebar') ?>

        <div id="main" class='layout-navbar'>
            <?= $this->include('layouts/navbar') ?>

            <div id="main-content" class="pt-0">
                <div class="page-heading">
                    <?= $this->renderSection('content') ?>
                </div>

                <?= $this->include('layouts/footer') ?>
            </div>
        </div>
    </div>

    <!-- Modal Notifikasi Sesi Habis -->
    <div class="modal fade" id="modalSessionExpired" tabindex="-1" aria-labelledby="modalSessionExpiredLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-body text-center p-4">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; border-radius: 50%; background-color: rgba(255, 193, 7, 0.15); color: #eaca4a;">
                        <i class="bi bi-clock-history" style="font-size: 2rem; line-height: 1; display: inline-flex; align-items: center; justify-content: center; width: auto; height: auto;"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-2" id="modalSessionExpiredLabel">Sesi Telah Habis</h5>
                    <p class="text-muted mb-4" style="font-size: 0.92rem; line-height: 1.5;">
                        Sesi Anda telah berakhir karena tidak ada aktivitas selama 30 menit. Demi keamanan data akun Anda, silakan masuk kembali.
                    </p>
                    <div class="d-grid">
                        <a href="<?= base_url('/logout') ?>" class="btn btn-primary py-2 fw-semibold" id="btnSessionRelogin" style="border-radius: 8px;">
                            Login ulang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
    <script>
        (() => {
            const idleTimeout = 30 * 60 * 1000;
            const logoutUrl = <?= json_encode(base_url('/logout')) ?>;
            const activityUrl = <?= json_encode(base_url('/session/activity')) ?>;
            const serverSyncInterval = 60 * 1000;
            const activityStorageKey = 'authenticated-last-activity';
            const activityEvents = ['click', 'keydown', 'pointerdown', 'scroll', 'touchstart'];
            let idleTimer;
            let lastActivityEvent = 0;
            let lastServerSync = Date.now();
            let isSessionExpired = false;

            const showSessionExpiredModal = () => {
                if (isSessionExpired) return;
                isSessionExpired = true;

                // Stop activity tracking
                window.clearTimeout(idleTimer);
                activityEvents.forEach((eventName) => {
                    document.removeEventListener(eventName, registerActivity);
                });
                localStorage.removeItem(activityStorageKey);

                // Notify server in background to destroy session
                fetch(logoutUrl, { method: 'GET', keepalive: true }).catch(() => {});

                // Apply sidebar-style backdrop class
                document.body.classList.add('session-expired-active');

                const modalEl = document.getElementById('modalSessionExpired');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modalInstance.show();
                } else {
                    window.location.assign(logoutUrl);
                }
            };

            const resetIdleTimer = () => {
                if (isSessionExpired) return;
                window.clearTimeout(idleTimer);
                const lastActivity = Number(localStorage.getItem(activityStorageKey)) || Date.now();
                const remainingTime = Math.max(0, idleTimeout - (Date.now() - lastActivity));
                idleTimer = window.setTimeout(() => {
                    const latestActivity = Number(localStorage.getItem(activityStorageKey)) || 0;
                    if (Date.now() - latestActivity < idleTimeout) {
                        resetIdleTimer();
                        return;
                    }

                    showSessionExpiredModal();
                }, remainingTime);
            };

            const registerActivity = () => {
                if (isSessionExpired) return;
                const now = Date.now();
                if (now - lastActivityEvent < 1000) {
                    return;
                }

                lastActivityEvent = now;
                localStorage.setItem(activityStorageKey, String(now));
                resetIdleTimer();

                if (now - lastServerSync >= serverSyncInterval) {
                    lastServerSync = now;
                    fetch(activityUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                        },
                        credentials: 'same-origin',
                        keepalive: true,
                    }).then((response) => {
                        if (response.redirected || response.status === 401) {
                            showSessionExpiredModal();
                        }
                    }).catch(() => {
                        // The server-side timeout remains authoritative if synchronization fails.
                    });
                }
            };

            activityEvents.forEach((eventName) => {
                document.addEventListener(eventName, registerActivity, {
                    passive: true
                });
            });

            window.addEventListener('storage', (event) => {
                if (event.key === activityStorageKey) {
                    if (event.newValue === null) {
                        showSessionExpiredModal();
                    } else {
                        resetIdleTimer();
                    }
                }
            });

            localStorage.setItem(activityStorageKey, String(Date.now()));
            resetIdleTimer();
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleDark = document.getElementById('toggle-dark');
            const savedTheme = localStorage.getItem('theme') || 'light';

            if (toggleDark) {
                if (savedTheme === 'dark') {
                    toggleDark.checked = true;
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                    document.body.classList.add('theme-dark');
                } else {
                    toggleDark.checked = false;
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                    document.body.classList.remove('theme-dark');
                }

                toggleDark.addEventListener('change', function() {
                    if (this.checked) {
                        localStorage.setItem('theme', 'dark');
                        document.documentElement.setAttribute('data-bs-theme', 'dark');
                        document.body.classList.add('theme-dark');
                    } else {
                        localStorage.setItem('theme', 'light');
                        document.documentElement.setAttribute('data-bs-theme', 'light');
                        document.body.classList.remove('theme-dark');
                    }
                });
            }
        });
    </script>
    <script>
        // Force refresh from server when page is restored from browser BFCache or back/forward navigation
        window.addEventListener('pageshow', function(event) {
            const isBfCache = event.persisted;
            const navEntry = window.performance && window.performance.getEntriesByType ?
                window.performance.getEntriesByType('navigation')[0] :
                null;
            const isBackForwardNav = navEntry && navEntry.type === 'back_forward';

            if (isBfCache || isBackForwardNav) {
                window.location.reload();
            }
        });
    </script>
    <script>
        document.addEventListener('show.bs.modal', function(event) {
            const modal = event.target;
            const cooldownBtn = modal.querySelector('[data-cooldown]');
            if (!cooldownBtn) return;

            const initialText = cooldownBtn.getAttribute('data-original-text') || cooldownBtn.textContent.trim();
            if (!cooldownBtn.getAttribute('data-original-text')) {
                cooldownBtn.setAttribute('data-original-text', initialText);
            }

            let duration = parseInt(cooldownBtn.getAttribute('data-cooldown'), 10) || 3;
            cooldownBtn.disabled = true;
            cooldownBtn.textContent = `${initialText} (${duration}s)`;

            let timer = setInterval(function() {
                duration--;
                if (duration > 0) {
                    cooldownBtn.textContent = `${initialText} (${duration}s)`;
                } else {
                    clearInterval(timer);
                    cooldownBtn.disabled = false;
                    cooldownBtn.textContent = initialText;
                }
            }, 1000);

            const onModalHidden = function() {
                clearInterval(timer);
                cooldownBtn.disabled = true;
                cooldownBtn.textContent = initialText;
                modal.removeEventListener('hidden.bs.modal', onModalHidden);
            };

            modal.addEventListener('hidden.bs.modal', onModalHidden);
        });
    </script>
    <script>
        (function() {
            let activeSubmitBtn = null;

            // Track which submit button triggered the submission
            document.addEventListener('click', function(event) {
                const btn = event.target.closest('button[type="submit"], input[type="submit"]');
                if (btn) {
                    activeSubmitBtn = btn;
                }
            }, true);

            // Handle form submission to show loading spinner
            document.addEventListener('submit', function(event) {
                const form = event.target;
                if (!form || form.nodeName !== 'FORM') return;

                // Skip forms that opt out or target a new tab/window
                if (form.getAttribute('data-no-loader') === 'true' || form.target === '_blank') {
                    return;
                }

                // Check HTML5 validity
                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                    return;
                }

                if (event.defaultPrevented) {
                    return;
                }

                // Find active or first submit button in form
                let submitBtn = activeSubmitBtn;
                if (!submitBtn || !form.contains(submitBtn)) {
                    submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                }

                if (!submitBtn || submitBtn.getAttribute('data-is-loading') === 'true') {
                    return;
                }

                // Preserve original markup and button width
                submitBtn.setAttribute('data-is-loading', 'true');
                submitBtn.setAttribute('data-original-html', submitBtn.innerHTML);

                const currentWidth = submitBtn.getBoundingClientRect().width;
                if (currentWidth > 0) {
                    submitBtn.style.minWidth = currentWidth + 'px';
                }

                // Option 2 (Adaptive):
                // Narrow buttons (< 115px) only show centered spinner to prevent text overflow/clipping.
                // Wider buttons show spinner + text.
                const customLoadingText = submitBtn.getAttribute('data-loading-text');
                const isNarrowButton = currentWidth > 0 && currentWidth < 115;
                let loaderHtml = '';

                if (isNarrowButton && !customLoadingText) {
                    loaderHtml = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                } else {
                    const loadingText = customLoadingText || 'Memproses...';
                    loaderHtml = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + loadingText;
                }

                // Defer disabling to the next tick so browser dispatches the submit request properly
                setTimeout(function() {
                    submitBtn.innerHTML = loaderHtml;
                    submitBtn.disabled = true;
                }, 0);
            });

            // Reset loading state when page restored from back/forward cache
            window.addEventListener('pageshow', function() {
                document.querySelectorAll('[data-is-loading="true"]').forEach(function(btn) {
                    const originalHtml = btn.getAttribute('data-original-html');
                    if (originalHtml) {
                        btn.innerHTML = originalHtml;
                    }
                    btn.disabled = false;
                    btn.removeAttribute('data-is-loading');
                    btn.style.minWidth = '';
                });
                activeSubmitBtn = null;
            });

            // Reset buttons when modal is dismissed without submitting
            document.addEventListener('hidden.bs.modal', function(event) {
                const modal = event.target;
                if (!modal) return;
                modal.querySelectorAll('[data-is-loading="true"]').forEach(function(btn) {
                    const originalHtml = btn.getAttribute('data-original-html');
                    if (originalHtml) {
                        btn.innerHTML = originalHtml;
                    }
                    btn.disabled = false;
                    btn.removeAttribute('data-is-loading');
                    btn.style.minWidth = '';
                });
            });
        })();
    </script>
</body>

</html>