<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\CriticalityRecoveryModel;
use App\Models\UserModel;
use Throwable;

class Application extends BaseController
{
    protected ApplicationModel $applicationModel;
    protected CriticalityRecoveryModel $criticalityRecoveryModel;
    protected UserModel $userModel;

    public function __construct()
    {
        helper(['form']);
        $this->applicationModel = new ApplicationModel();
        $this->criticalityRecoveryModel = new CriticalityRecoveryModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $criticality = $this->request->getGet('criticality_recovery_id');
        $criticality = is_numeric($criticality) ? (int) $criticality : null;

        return view('application/index', [
            'title' => 'Aplikasi Pengelolaan',
            'applications' => $this->applicationModel->getApplicationsWithDetails($criticality, $keyword ?: null),
            'criticalityOptions' => $this->criticalityRecoveryModel->getActiveOptions(),
            'keyword' => $keyword,
            'selectedCriticality' => $criticality,
        ]);
    }

    public function create()
    {
        return view('application/create', $this->formData(null));
    }

    public function detail($id)
    {
        $application = $this->applicationModel->getApplicationDetail((int) $id);
        if (!$application) {
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak ditemukan.');
        }

        return view('application/detail', ['title' => 'Detail Aplikasi', 'application' => $application]);
    }

    public function edit($id)
    {
        $application = $this->applicationModel->find((int) $id);
        if (!$application) {
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak ditemukan.');
        }

        return view('application/edit', $this->formData($application));
    }

    public function store()
    {
        $payload = $this->validatedPayload();
        if ($payload === null) {
            return redirect()->to('/aplikasi/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            if (!$this->applicationModel->insert($payload)) {
                return redirect()->to('/aplikasi/create')->withInput()->with('error', 'Aplikasi gagal disimpan. Silakan periksa data dan coba lagi.');
            }
        } catch (Throwable $exception) {
            log_message('error', 'Gagal menyimpan aplikasi: {message}', ['message' => $exception->getMessage()]);
            return redirect()->to('/aplikasi/create')->withInput()->with('error', 'Terjadi gangguan saat menyimpan aplikasi.');
        }

        return redirect()->to('/aplikasi')->with('success', 'Aplikasi berhasil ditambahkan.');
    }

    public function update($id)
    {
        $id = (int) $id;
        if (!$this->applicationModel->find($id)) {
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak ditemukan.');
        }

        $payload = $this->validatedPayload();
        if ($payload === null) {
            return redirect()->to('/aplikasi/edit/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            if (!$this->applicationModel->update($id, $payload)) {
                return redirect()->to('/aplikasi/edit/' . $id)->withInput()->with('error', 'Aplikasi gagal diperbarui.');
            }
        } catch (Throwable $exception) {
            log_message('error', 'Gagal memperbarui aplikasi {id}: {message}', ['id' => $id, 'message' => $exception->getMessage()]);
            return redirect()->to('/aplikasi/edit/' . $id)->withInput()->with('error', 'Terjadi gangguan saat memperbarui aplikasi.');
        }

        return redirect()->to('/aplikasi/detail/' . $id)->with('success', 'Aplikasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $id = (int) $id;
        if (!$this->applicationModel->find($id)) {
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak ditemukan.');
        }

        try {
            if (!$this->applicationModel->delete($id)) {
                return redirect()->to('/aplikasi')->with('error', 'Aplikasi gagal dihapus.');
            }
        } catch (Throwable $exception) {
            log_message('error', 'Gagal menghapus aplikasi {id}: {message}', ['id' => $id, 'message' => $exception->getMessage()]);
            return redirect()->to('/aplikasi')->with('error', 'Aplikasi tidak dapat dihapus karena masih digunakan atau terjadi gangguan database.');
        }
        return redirect()->to('/aplikasi')->with('success', 'Aplikasi berhasil dihapus.');
    }

    private function formData(?array $application): array
    {
        return [
            'title' => $application ? 'Edit Aplikasi' : 'Tambah Aplikasi',
            'application' => $application,
            'criticalityOptions' => $this->criticalityRecoveryModel->getActiveOptions(),
            'users' => $this->userModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
            'formAction' => base_url($application ? '/aplikasi/update/' . $application['id'] : '/aplikasi/store'),
            'submitLabel' => $application ? 'Simpan Perubahan' : 'Simpan Aplikasi',
        ];
    }

    /** Validate references and normalize all user-editable fields before persistence. */
    private function validatedPayload(): ?array
    {
        $rules = [
            'app_component' => 'required|max_length[150]',
            'criticality_recovery_id' => 'required|is_natural_no_zero',
            'assigned_user_id' => 'permit_empty|is_natural_no_zero',
            'url_prod' => 'permit_empty|valid_url',
            'url_dev' => 'permit_empty|valid_url',
            'url_uat' => 'permit_empty|valid_url',
        ];
        if (!$this->validate($rules)) {
            return null;
        }

        $criticality = (int) $this->request->getPost('criticality_recovery_id');
        $pic = $this->request->getPost('assigned_user_id');
        if (!$this->criticalityRecoveryModel->where('id', $criticality)->where('is_active', 1)->first()) {
            $this->validator->setError('criticality_recovery_id', 'Criticality yang dipilih tidak valid.');
            return null;
        }
        if ($pic !== null && $pic !== '' && !$this->userModel->where('id', (int) $pic)->where('is_active', 1)->first()) {
            $this->validator->setError('assigned_user_id', 'PIC yang dipilih tidak valid.');
            return null;
        }

        $fields = ['app_component', 'description', 'app_type', 'arch_type', 'access_type', 'login_auth', 'platform', 'url_prod', 'url_dev', 'url_uat', 'development_type', 'vendor', 'license_scheme', 'deployment_type', 'business_owner', 'system_owner'];
        $payload = [];
        foreach ($fields as $field) {
            $value = trim((string) $this->request->getPost($field));
            $payload[$field] = $value !== '' ? $value : null;
        }
        $payload['criticality_recovery_id'] = $criticality;
        $payload['assigned_user_id'] = ($pic !== null && $pic !== '') ? (int) $pic : null;
        $payload['has_source_code'] = $this->request->getPost('has_source_code') ? 1 : 0;

        return $payload;
    }
}
