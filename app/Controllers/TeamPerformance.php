<?php

namespace App\Controllers;

use App\Libraries\DashboardMetrics;

class TeamPerformance extends BaseController
{
    public function __construct()
    {
        helper(['project_filter']);
    }

    public function index()
    {
        if (session()->get('role_name') !== 'Kepala Departemen') {
            return $this->render403('Anda tidak memiliki akses menuju halaman ini. Silahkan kembali ke halaman sebelumnya');
        }

        $startDate = trim((string) $this->request->getGet('filter_start'));
        $endDate = trim((string) $this->request->getGet('filter_end'));
        $range = resolve_project_date_range($startDate, $endDate);

        $metrics = (new DashboardMetrics())->team($range);

        return view('team_performance/index', [
            'title'              => 'Kinerja Tim',
            'metrics'            => $metrics,
            'selectedStartDate'  => $range['start_date'] ?? $startDate,
            'selectedEndDate'    => $range['end_date'] ?? $endDate,
        ]);
    }
}
