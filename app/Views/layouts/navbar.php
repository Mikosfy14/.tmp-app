<?php
$profileName = trim((string) session()->get('name'));
$profileInitial = strtoupper(substr($profileName !== '' ? $profileName : 'U', 0, 1));
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

    .navbar-profile-menu {
        min-width: 220px;
        z-index: 1080;
    }

    [data-bs-theme="dark"] .navbar-profile-trigger {
        border-color: #252539;
    }
</style>

<header>
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="ms-auto d-flex align-items-center">
                    <div class="user-menu dropdown">
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
