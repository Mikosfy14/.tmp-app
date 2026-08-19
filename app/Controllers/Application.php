<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\CriticalityRecoveryModel;
use App\Models\UserModel;

class Application extends BaseController
{
    protected $applicationModel;
    protected $criticalityRecoveryModel;
    protected $userModel;

    public function __construct()
    {
        $this->applicationModel = new ApplicationModel();
        $this->criticalityRecoveryModel = new CriticalityRecoveryModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $criticalityFilter = $this->request->getGet('criticality_recovery_id');
        $keyword = $this->request->getGet('keyword');
        $applications = $this->applicationModel->getApplicationsWithDetails(
            $criticalityFilter !== null && $criticalityFilter !== '' ? (int) $criticalityFilter : null,
            $keyword !== null && $keyword !== '' ? trim((string) $keyword) : null
        );

        if (empty($applications)) {
            $applications = [
                [
                    'id' => 1,
                    'app_component' => 'Portal HRD & Absensi',
                    'description' => 'Sistem informasi SDM, absensi, cuti, dan penggajian internal.',
                    'app_type' => 'Application',
                    'arch_type' => 'Monolithic',
                    'criticality_recovery' => 'Criticality 2',
                    'criticality_recovery_description' => 'Essential Functions',
                    'access_type' => 'Internal access',
                    'login_auth' => 'User AD',
                    'platform' => 'Web app',
                    'url_prod' => 'https://hrd.tmp.local',
                    'url_dev' => 'https://dev-hrd.tmp.local',
                    'url_uat' => 'https://uat-hrd.tmp.local',
                    'development_type' => 'Internal',
                    'vendor' => null,
                    'license_scheme' => 'No license',
                    'deployment_type' => 'On premise',
                    'business_owner' => 'HC Operations',
                    'system_owner' => 'IT Application',
                    'has_source_code' => 1,
                    'assigned_user_id' => 2,
                    'assigned_user_name' => 'Ahmad Rizki',
                    'created_at' => '2026-08-01 09:00:00',
                    'updated_at' => '2026-08-12 14:30:00',
                ],
                [
                    'id' => 2,
                    'app_component' => 'API Gateway Service',
                    'description' => 'Gateway integrasi layanan internal dan partner channel.',
                    'app_type' => 'Services',
                    'arch_type' => 'Microservices',
                    'criticality_recovery' => 'Criticality 1',
                    'criticality_recovery_description' => 'Critical Functions',
                    'access_type' => 'Public with internal',
                    'login_auth' => 'MFA',
                    'platform' => 'Hybrid',
                    'url_prod' => 'https://api.tmp.local',
                    'url_dev' => 'https://dev-api.tmp.local',
                    'url_uat' => 'https://uat-api.tmp.local',
                    'development_type' => 'Both',
                    'vendor' => 'TMP Integration Partner',
                    'license_scheme' => 'Subscription',
                    'deployment_type' => 'Hybrid',
                    'business_owner' => 'Digital Business',
                    'system_owner' => 'Platform Engineering',
                    'has_source_code' => 1,
                    'assigned_user_id' => 3,
                    'assigned_user_name' => 'Siti Nurhaliza',
                    'created_at' => '2026-08-03 11:20:00',
                    'updated_at' => '2026-08-13 10:15:00',
                ],
                [
                    'id' => 3,
                    'app_component' => 'Legacy Report App',
                    'description' => 'Aplikasi laporan legacy yang masih dipakai operasional bulanan.',
                    'app_type' => 'Application',
                    'arch_type' => 'Monolithic',
                    'criticality_recovery' => 'Criticality 4',
                    'criticality_recovery_description' => 'Desirable Functions',
                    'access_type' => 'Internal access',
                    'login_auth' => 'Non User AD',
                    'platform' => 'Web app',
                    'url_prod' => 'http://192.168.10.99',
                    'url_dev' => null,
                    'url_uat' => null,
                    'development_type' => 'COTS',
                    'vendor' => 'Legacy Vendor',
                    'license_scheme' => 'Perpetual',
                    'deployment_type' => 'On premise',
                    'business_owner' => 'Finance Reporting',
                    'system_owner' => 'IT Application',
                    'has_source_code' => 0,
                    'assigned_user_id' => 4,
                    'assigned_user_name' => 'Budi Santoso',
                    'created_at' => '2026-07-28 08:45:00',
                    'updated_at' => null,
                ],
                [
                    'id' => 4,
                    'app_component' => 'Mobile Approval Suite',
                    'description' => 'Aplikasi mobile untuk approval request operasional dan workflow.',
                    'app_type' => 'Application',
                    'arch_type' => 'Cloud',
                    'criticality_recovery' => 'Criticality 3',
                    'criticality_recovery_description' => 'Necessary Functions',
                    'access_type' => 'Public access',
                    'login_auth' => 'Userauth',
                    'platform' => 'Mobile',
                    'url_prod' => 'https://approval.tmp.local',
                    'url_dev' => 'https://dev-approval.tmp.local',
                    'url_uat' => 'https://uat-approval.tmp.local',
                    'development_type' => 'External',
                    'vendor' => 'Mobile Solution Vendor',
                    'license_scheme' => 'Subscription',
                    'deployment_type' => 'Cloud',
                    'business_owner' => 'Operations',
                    'system_owner' => 'IT Application',
                    'has_source_code' => 0,
                    'assigned_user_id' => 2,
                    'assigned_user_name' => 'Ahmad Rizki',
                    'created_at' => '2026-08-05 13:10:00',
                    'updated_at' => '2026-08-10 16:50:00',
                ],
            ];
        }

        $users = $this->userModel->where('is_active', 1)->findAll();

        return view('application/index', [
            'title' => 'Aplikasi Pengelolaan',
            'applications' => $applications,
            'users' => $users,
            'criticalityOptions' => $this->criticalityRecoveryModel->getActiveOptions(),
        ]);
    }
}
