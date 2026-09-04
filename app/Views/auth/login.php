<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon / Title Bar Icon -->
    <link rel="shortcut icon" href="<?= base_url('assets/images/logo/tmp_logo.png') ?>" type="image/png">
    <title>Login - .tmp Project Management</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            overflow-x: hidden;
        }

        /* Left Split-Screen Column */
        .auth-brand-side {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.90), rgba(15, 23, 42, 0.60)),
                        url('<?= base_url('assets/images/bg/login-bg.png') ?>') center center / cover no-repeat;
            color: #ffffff;
            position: relative;
        }

        .brand-badge-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .brand-badge-icon img {
            height: 72px;
            width: auto;
            object-fit: contain;
        }

        /* Right Split-Screen Column - Solid Clean White */
        .auth-form-side {
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-form-container {
            width: 100%;
            max-width: 400px;
        }

        /* Direct Clean Logo in Form */
        .form-brand-logo-container {
            display: inline-block;
        }

        .form-brand-logo-container img {
            height: 72px;
            width: auto;
            object-fit: contain;
        }

        /* Seamless Input Group */
        .seamless-input-group {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            overflow: hidden;
        }

        .seamless-input-group:focus-within {
            background-color: #ffffff;
            border-color: #435ebe;
            box-shadow: 0 0 0 3px rgba(67, 94, 190, 0.15);
        }

        .seamless-input-group .input-group-text {
            background-color: transparent !important;
            border: none !important;
            color: #236acf;
        }

        .seamless-input-group .form-control {
            background-color: transparent !important;
            border: none !important;
            color: #272d35;
            font-size: 0.95rem;
            padding: 10px 12px 10px 4px;
        }

        .seamless-input-group .form-control:focus {
            box-shadow: none !important;
        }

        .seamless-input-group .btn-toggle-password {
            background-color: transparent !important;
            border: none !important;
            color: #94a3b8;
            padding: 0 14px;
            transition: color 0.15s ease;
        }

        .seamless-input-group .btn-toggle-password:hover {
            color: #475569;
        }

        .btn-submit-login {
            background-color: #435ebe;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #ffffff;
            transition: background-color 0.15s ease, transform 0.1s ease;
        }

        .btn-submit-login:hover:not(:disabled) {
            background-color: #354da8;
            color: #ffffff;
        }

        .btn-submit-login:disabled {
            opacity: 0.75;
            cursor: not-allowed;
        }

        .caps-lock-badge {
            font-size: 0.76rem;
            border-radius: 6px;
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
            padding: 4px 8px;
        }
    </style>
</head>

<body>

    <div class="container-fluid p-0 min-vh-100 d-flex flex-column flex-lg-row">
        
        <!-- KOLOM KIRI: Identitas Sistem (Layar Desktop) -->
        <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 auth-brand-side">
            <!-- Header Kosong untuk Spacing -->
            <div></div>

            <!-- Konten Tengah: Logo, Nama Web, dan Sub Judul -->
            <div class="my-auto text-center px-4" style="max-width: 520px; margin-inline: auto;">
                <div class="mb-4">
                    <img src="<?= base_url('assets/images/logo/tmp_logo.png') ?>" alt="Logo .tmp" style="height: 100px; width: auto; object-fit: contain;">
                </div>
                <h2 class="fw-bold text-white mb-2" style="font-size: 2rem; letter-spacing: -0.5px;">.tmp Project Management</h2>
                <p class="text-white-50 fs-6 mb-0" style="line-height: 1.6;">
                    Sistem pemantauan project dan tata kelola katalog aplikasi departemen.
                </p>
            </div>

            <!-- Footer Kolom Kiri -->
            <div class="text-center text-white-50 small">
                &copy; <?= date('Y') ?> .tmp Project Management
            </div>
        </div>

        <!-- KOLOM KANAN: Form Login -->
        <div class="col-12 col-lg-6 auth-form-side p-4 p-sm-5 min-vh-100">
            <div class="auth-form-container py-4">
                
                <!-- Logo Container & Header Form -->
                <div class="mb-4">
                    <h4 class="fw-bold text-dark mb-1">Masuk ke Sistem</h4>
                    <p class="text-muted small mb-0">Silahkan masukkan kredensial Anda.</p>
                </div>

                <!-- Session Flash Messages -->
                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show small d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
                        <div class="flex-grow-1"><?= session()->getFlashdata('error') ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="alert alert-success alert-dismissible fade show small d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-check flex-shrink-0"></i>
                        <div class="flex-grow-1"><?= session()->getFlashdata('success') ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form action="<?= base_url('/login/attempt') ?>" method="POST" id="formLogin" novalidate>
                    <?= csrf_field() ?>

                    <!-- Username Field -->
                    <div class="mb-3">
                        <label for="username" class="form-label text-secondary fw-semibold small mb-1">Username</label>
                        <div class="input-group seamless-input-group">
                            <span class="input-group-text ps-3">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="text"
                                name="username"
                                id="username"
                                class="form-control"
                                placeholder="Masukkan username"
                                value="<?= old('username') ?>"
                                required
                                autocomplete="username"
                                autofocus>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label text-secondary fw-semibold small mb-0">Password</label>
                        </div>
                        <div class="input-group seamless-input-group">
                            <span class="input-group-text ps-3">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password">
                            <button class="btn btn-toggle-password" type="button" id="togglePassword" aria-label="Toggle password visibility">
                                <i class="fa-solid fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>

                        <!-- Caps Lock Alert -->
                        <div id="capsLockAlert" class="d-none mt-2 caps-lock-badge d-flex align-items-center gap-2" role="alert">
                            <i class="fa-solid fa-triangle-exclamation text-warning flex-shrink-0"></i>
                            <span><strong>Capslock aktif:</strong> Periksa huruf kapital password.</span>
                        </div>
                    </div>

                    <!-- Submit Button with Loading State -->
                    <button type="submit" id="btnLogin" class="btn btn-submit-login w-100 mt-4 d-flex align-items-center justify-content-center gap-2">
                        <span id="btnText">Masuk ke Sistem</span>
                        <i class="fa-solid fa-arrow-right-to-bracket" id="btnIcon"></i>
                        <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </form>

                <!-- Mobile Copyright Footer -->
                <div class="text-center text-white small mt-4 d-block d-lg-none">
                    &copy; <?= date('Y') ?> .tmp Project Management.
                </div>

            </div>
        </div>

    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Revalidate session when browser restores page from back/forward cache
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        // Toggle Password Visibility
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        if (togglePassword && passwordInput && eyeIcon) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon.classList.toggle('fa-eye');
                eyeIcon.classList.toggle('fa-eye-slash');
            });
        }

        // Caps Lock Detection
        const capsLockAlert = document.querySelector('#capsLockAlert');
        if (passwordInput && capsLockAlert) {
            const checkCapsLock = function(e) {
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    capsLockAlert.classList.remove('d-none');
                } else {
                    capsLockAlert.classList.add('d-none');
                }
            };

            passwordInput.addEventListener('keydown', checkCapsLock);
            passwordInput.addEventListener('keyup', checkCapsLock);
            passwordInput.addEventListener('focus', checkCapsLock);
            passwordInput.addEventListener('blur', function() {
                capsLockAlert.classList.add('d-none');
            });
        }

        // Button Loading State on Form Submit
        const formLogin = document.querySelector('#formLogin');
        const btnLogin = document.querySelector('#btnLogin');
        const btnText = document.querySelector('#btnText');
        const btnIcon = document.querySelector('#btnIcon');
        const btnSpinner = document.querySelector('#btnSpinner');

        if (formLogin && btnLogin) {
            formLogin.addEventListener('submit', function(e) {
                const usernameInput = document.querySelector('#username');
                
                // Only trigger loading if basic inputs are filled
                if (usernameInput && usernameInput.value.trim() !== '' && passwordInput && passwordInput.value !== '') {
                    btnLogin.disabled = true;
                    if (btnText) btnText.textContent = 'Memverifikasi...';
                    if (btnIcon) btnIcon.classList.add('d-none');
                    if (btnSpinner) btnSpinner.classList.remove('d-none');
                    
                    // Allow native form submission
                    this.submit();
                }
            });
        }
    </script>
</body>

</html>
