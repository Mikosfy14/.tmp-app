<?php
/**
 * @var array $applications
 * @var array $users
 */

$applications = $applications ?? [];
$users = $users ?? [];

$criticalityClass = static function (?string $criticality): string {
    return match ($criticality) {
        'Category 1' => 'danger',
        'Category 2' => 'warning',
        'Category 3' => 'info',
        'Category 4' => 'success',
        default => 'secondary',
    };
};

$valueOrDash = static fn ($value): string => $value !== null && $value !== '' ? esc($value) : '-';

$totalApplications = count($applications);
$sourceCodeCount = count(array_filter($applications, static fn ($app) => (int) $app['has_source_code'] === 1));
$criticalApps = count(array_filter($applications, static fn ($app) => in_array($app['criticality_recovery'], ['Category 1', 'Category 2'], true)));
$managedUsers = count(array_unique(array_filter(array_column($applications, 'assigned_user_id'))));
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-heading d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3><i class="bi bi-stack me-2 text-primary"></i>Aplikasi Pengelolaan</h3>
        <p class="text-subtitle text-muted mb-0">Katalog aplikasi dan services yang dikelola oleh tim.</p>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalApplicationForm">
        <i class="bi bi-plus-lg me-1"></i> Tambah Aplikasi
    </button>
</div>

<div class="page-content">
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body py-3">
                    <span class="text-muted text-sm fw-semibold">Total Aplikasi</span>
                    <h3 class="fw-bold mb-0 text-primary"><?= $totalApplications ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body py-3">
                    <span class="text-muted text-sm fw-semibold">Critical Assets</span>
                    <h3 class="fw-bold mb-0 text-danger"><?= $criticalApps ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body py-3">
                    <span class="text-muted text-sm fw-semibold">Source Code Ada</span>
                    <h3 class="fw-bold mb-0 text-success"><?= $sourceCodeCount ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-sm h-100 mb-0">
                <div class="card-body py-3">
                    <span class="text-muted text-sm fw-semibold">PIC Aktif</span>
                    <h3 class="fw-bold mb-0 text-info"><?= $managedUsers ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                        <input type="text" id="applicationSearch" class="form-control" placeholder="Cari aplikasi, owner, PIC, URL...">
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <select id="criticalityFilter" class="form-select">
                        <option value="">Semua Criticality</option>
                        <option value="Category 1">Category 1</option>
                        <option value="Category 2">Category 2</option>
                        <option value="Category 3">Category 3</option>
                        <option value="Category 4">Category 4</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <select id="sourceCodeFilter" class="form-select">
                        <option value="">Semua Source Code</option>
                        <option value="1">Ada Source Code</option>
                        <option value="0">Tidak Ada Source Code</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <button type="button" id="applicationFilterReset" class="btn btn-outline-secondary w-100">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="applicationsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Aplikasi / Services</th>
                            <th>Criticality</th>
                            <th>Platform</th>
                            <th>Deployment</th>
                            <th>Auth / Access</th>
                            <th>PIC</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($applications)) : ?>
                            <?php foreach ($applications as $app) : ?>
                                <?php
                                $criticalityColor = $criticalityClass($app['criticality_recovery']);
                                $searchText = strtolower(implode(' ', array_filter([
                                    $app['app_component'],
                                    $app['description'],
                                    $app['business_owner'],
                                    $app['system_owner'],
                                    $app['assigned_user_name'],
                                    $app['url_prod'],
                                    $app['url_dev'],
                                    $app['url_uat'],
                                ])));
                                ?>
                                <tr class="application-row"
                                    data-search="<?= esc($searchText) ?>"
                                    data-criticality="<?= esc($app['criticality_recovery'] ?? '') ?>"
                                    data-source-code="<?= (int) $app['has_source_code'] ?>">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar bg-light-primary text-primary rounded d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                <i class="bi bi-window-stack fs-5"></i>
                                            </div>
                                            <div>
                                                <strong class="d-block text-dark"><?= esc($app['app_component']) ?></strong>
                                                <small class="text-muted d-inline-block text-truncate" style="max-width: 320px;"><?= esc($app['description']) ?></small>
                                                <div class="mt-1 d-flex flex-wrap gap-1">
                                                    <span class="badge bg-light-secondary text-secondary"><?= esc($app['app_type']) ?></span>
                                                    <span class="badge bg-light-primary text-primary"><?= esc($app['arch_type']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-light-<?= $criticalityColor ?> text-<?= $criticalityColor ?>"><?= esc($app['criticality_recovery']) ?></span>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-semibold text-dark"><?= esc($app['platform']) ?></span>
                                        <small class="d-block text-muted"><?= esc($app['development_type']) ?></small>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-semibold text-dark"><?= esc($app['deployment_type']) ?></span>
                                        <small class="d-block text-muted"><?= esc($app['license_scheme']) ?></small>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-semibold text-dark"><?= esc($app['login_auth']) ?></span>
                                        <small class="d-block text-muted"><?= esc($app['access_type']) ?></small>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-semibold text-dark"><?= esc($app['assigned_user_name']) ?></span>
                                        <small class="d-block text-muted"><?= esc($app['system_owner']) ?></small>
                                    </td>
                                    <td class="text-center pe-4 py-3">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalApplicationDetail<?= $app['id'] ?>" title="Detail">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light-secondary" data-bs-toggle="modal" data-bs-target="#modalApplicationForm" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="applicationsEmptyRow" class="<?= empty($applications) ? '' : 'd-none' ?>">
                            <td colspan="7" class="text-center py-4 text-muted">Data aplikasi tidak ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php foreach ($applications as $app) : ?>
    <?php $criticalityColor = $criticalityClass($app['criticality_recovery']); ?>
    <div class="modal fade" id="modalApplicationDetail<?= $app['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title text-white mb-0"><?= esc($app['app_component']) ?></h5>
                        <small class="text-white-50"><?= esc($app['app_type']) ?> / <?= esc($app['platform']) ?></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-12 col-lg-7">
                            <h6 class="fw-bold text-primary mb-3">Informasi Aplikasi</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Criticality Recovery</small>
                                    <span class="badge bg-light-<?= $criticalityColor ?> text-<?= $criticalityColor ?>"><?= esc($app['criticality_recovery']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Source Code</small>
                                    <?php if ((int) $app['has_source_code'] === 1) : ?>
                                        <span class="badge bg-success">Ada</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">Tidak Ada</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Architecture</small>
                                    <strong class="text-dark"><?= $valueOrDash($app['arch_type']) ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Deployment</small>
                                    <strong class="text-dark"><?= $valueOrDash($app['deployment_type']) ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Development Type</small>
                                    <strong class="text-dark"><?= $valueOrDash($app['development_type']) ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Vendor</small>
                                    <strong class="text-dark"><?= $valueOrDash($app['vendor']) ?></strong>
                                </div>
                            </div>

                            <hr>

                            <h6 class="fw-bold text-primary mb-3">URL Environment</h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0 d-flex justify-content-between gap-3">
                                    <span class="text-muted">Production</span>
                                    <span class="font-monospace text-end"><?= $valueOrDash($app['url_prod']) ?></span>
                                </div>
                                <div class="list-group-item px-0 d-flex justify-content-between gap-3">
                                    <span class="text-muted">Development</span>
                                    <span class="font-monospace text-end"><?= $valueOrDash($app['url_dev']) ?></span>
                                </div>
                                <div class="list-group-item px-0 d-flex justify-content-between gap-3">
                                    <span class="text-muted">UAT</span>
                                    <span class="font-monospace text-end"><?= $valueOrDash($app['url_uat']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <h6 class="fw-bold text-primary mb-3">Ownership & Access</h6>
                            <div class="p-3 border rounded mb-3">
                                <small class="text-muted d-block">Assigned PIC</small>
                                <strong class="text-dark"><?= esc($app['assigned_user_name']) ?></strong>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted d-block">Business Owner</small>
                                    <strong class="text-dark"><?= $valueOrDash($app['business_owner']) ?></strong>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">System Owner</small>
                                    <strong class="text-dark"><?= $valueOrDash($app['system_owner']) ?></strong>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Access Type</small>
                                    <strong class="text-dark"><?= $valueOrDash($app['access_type']) ?></strong>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Login Auth</small>
                                    <strong class="text-dark"><?= $valueOrDash($app['login_auth']) ?></strong>
                                </div>
                            </div>
                            <hr>
                            <small class="text-muted d-block">Deskripsi</small>
                            <p class="mb-0 text-dark"><?= esc($app['description']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="bi bi-pencil-square me-1"></i> Edit Data</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="modal fade" id="modalApplicationForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bi bi-window-plus me-2"></i>Form Aplikasi Pengelolaan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">App Component</label>
                        <input type="text" class="form-control" placeholder="Nama aplikasi atau service">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">App Type</label>
                        <select class="form-select">
                            <option>Application</option>
                            <option>Services</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Platform</label>
                        <select class="form-select">
                            <option>Web app</option>
                            <option>Mobile</option>
                            <option>Desktop clientapp</option>
                            <option>Hybrid</option>
                            <option>Device</option>
                            <option>Web app and mobile app</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" rows="2" placeholder="Deskripsi singkat aplikasi"></textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Architecture</label>
                        <select class="form-select">
                            <option>Microservices</option>
                            <option>Monolithic</option>
                            <option>Cloud</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Criticality</label>
                        <select class="form-select">
                            <option>Category 1</option>
                            <option>Category 2</option>
                            <option>Category 3</option>
                            <option>Category 4</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Access Type</label>
                        <select class="form-select">
                            <option>Internal access</option>
                            <option>Public access</option>
                            <option>Public with internal</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Login Auth</label>
                        <select class="form-select">
                            <option>User AD</option>
                            <option>Non User AD</option>
                            <option>Userauth</option>
                            <option>MFA</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">URL Production</label>
                        <input type="url" class="form-control" placeholder="https://app.tmp.local">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">URL Development</label>
                        <input type="url" class="form-control" placeholder="https://dev-app.tmp.local">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">URL UAT</label>
                        <input type="url" class="form-control" placeholder="https://uat-app.tmp.local">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Development Type</label>
                        <select class="form-select">
                            <option>Internal</option>
                            <option>External</option>
                            <option>Both</option>
                            <option>COTS</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">License Scheme</label>
                        <select class="form-select">
                            <option>No license</option>
                            <option>Perpetual</option>
                            <option>Subscription</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Deployment Type</label>
                        <select class="form-select">
                            <option>On premise</option>
                            <option>Cloud</option>
                            <option>SaaS</option>
                            <option>Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Vendor</label>
                        <input type="text" class="form-control" placeholder="Nama vendor">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Business Owner</label>
                        <input type="text" class="form-control" placeholder="Unit business owner">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">System Owner</label>
                        <input type="text" class="form-control" placeholder="System owner">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Assigned PIC</label>
                        <select class="form-select">
                            <?php foreach ($users as $user) : ?>
                                <option value="<?= $user['id'] ?>"><?= esc($user['name']) ?> (<?= esc($user['job_title']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="hasSourceCodeInput">
                            <label class="form-check-label fw-semibold" for="hasSourceCodeInput">Memiliki source code</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="bi bi-save me-1"></i> Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('applicationSearch');
        const criticalityFilter = document.getElementById('criticalityFilter');
        const sourceCodeFilter = document.getElementById('sourceCodeFilter');
        const resetButton = document.getElementById('applicationFilterReset');
        const rows = Array.from(document.querySelectorAll('.application-row'));
        const emptyRow = document.getElementById('applicationsEmptyRow');

        function applyApplicationFilters() {
            const keyword = (searchInput.value || '').trim().toLowerCase();
            const criticality = criticalityFilter.value;
            const sourceCode = sourceCodeFilter.value;
            let visibleCount = 0;

            rows.forEach(function(row) {
                const matchesSearch = !keyword || row.dataset.search.includes(keyword);
                const matchesCriticality = !criticality || row.dataset.criticality === criticality;
                const matchesSourceCode = !sourceCode || row.dataset.sourceCode === sourceCode;
                const isVisible = matchesSearch && matchesCriticality && matchesSourceCode;

                row.classList.toggle('d-none', !isVisible);
                if (isVisible) {
                    visibleCount++;
                }
            });

            emptyRow.classList.toggle('d-none', visibleCount > 0);
        }

        searchInput.addEventListener('input', applyApplicationFilters);
        criticalityFilter.addEventListener('change', applyApplicationFilters);
        sourceCodeFilter.addEventListener('change', applyApplicationFilters);
        resetButton.addEventListener('click', function() {
            searchInput.value = '';
            criticalityFilter.value = '';
            sourceCodeFilter.value = '';
            applyApplicationFilters();
        });
    });
</script>

<?= $this->endSection() ?>
