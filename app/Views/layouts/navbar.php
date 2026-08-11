<header>
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="ms-auto d-flex align-items-center">
                    <div class="user-menu d-flex">
                        <div class="user-name text-end me-3">
                            <h6 class="mb-0 text-gray-600"><?= esc(session()->get('name')) ?></h6>
                            <p class="mb-0 text-sm text-muted"><?= esc(session()->get('role_name')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>