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
    <link rel="shortcut icon" href="<?= base_url('assets/images/logo/tmp_logo.jpg') ?>" type="image/jpeg">
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

        #sidebar {
            position: relative;
            z-index: 1040;
        }

        #sidebar .sidebar-wrapper {
            left: 0 !important;
            z-index: 1041;
            box-shadow: 0 0 1.5rem rgba(20, 24, 40, .12);
            transition: transform .25s ease-out;
        }

        #sidebar:not(.active) .sidebar-wrapper,
        html.sidebar-closed #sidebar .sidebar-wrapper {
            transform: translateX(-100%);
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

        [data-bs-theme="dark"] .sidebar-wrapper .menu .sidebar-title {
            color: #565674 !important;
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

        [data-bs-theme="dark"] .bg-light,
        [data-bs-theme="dark"] .bg-white,
        [data-bs-theme="dark"] .bg-light-primary,
        [data-bs-theme="dark"] .bg-light-secondary,
        [data-bs-theme="dark"] .bg-light-success,
        [data-bs-theme="dark"] .bg-light-danger,
        [data-bs-theme="dark"] .bg-light-warning,
        [data-bs-theme="dark"] .bg-light-info {
            background-color: #252539 !important;
            color: #f5f7ff !important;
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
    <script>
        (() => {
            const idleTimeout = 15 * 60 * 1000;
            const logoutUrl = <?= json_encode(base_url('/logout')) ?>;
            const activityUrl = <?= json_encode(base_url('/session/activity')) ?>;
            const serverSyncInterval = 60 * 1000;
            const activityStorageKey = 'authenticated-last-activity';
            let idleTimer;
            let lastActivityEvent = 0;
            let lastServerSync = Date.now();

            const resetIdleTimer = () => {
                window.clearTimeout(idleTimer);
                const lastActivity = Number(localStorage.getItem(activityStorageKey)) || Date.now();
                const remainingTime = Math.max(0, idleTimeout - (Date.now() - lastActivity));
                idleTimer = window.setTimeout(() => {
                    const latestActivity = Number(localStorage.getItem(activityStorageKey)) || 0;
                    if (Date.now() - latestActivity < idleTimeout) {
                        resetIdleTimer();
                        return;
                    }

                    window.location.assign(logoutUrl);
                }, remainingTime);
            };

            const registerActivity = () => {
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
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        keepalive: true,
                    }).then((response) => {
                        if (response.redirected || response.status === 401) {
                            window.location.assign(logoutUrl);
                        }
                    }).catch(() => {
                        // The server-side timeout remains authoritative if synchronization fails.
                    });
                }
            };

            ['click', 'keydown', 'pointerdown', 'scroll', 'touchstart'].forEach((eventName) => {
                document.addEventListener(eventName, registerActivity, {
                    passive: true
                });
            });

            window.addEventListener('storage', (event) => {
                if (event.key === activityStorageKey) {
                    resetIdleTimer();
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
</body>

</html>