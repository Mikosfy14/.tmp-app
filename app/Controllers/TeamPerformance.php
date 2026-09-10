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
        $memberKeyword = trim((string) $this->request->getGet('member_keyword'));
        $range = resolve_project_date_range($startDate, $endDate);

        $metrics = (new DashboardMetrics())->team($range);

        $allMembers = $metrics['members'] ?? [];
        $totalRegisteredMembers = count($allMembers);

        if ($memberKeyword !== '') {
            $filteredMembers = [];
            foreach ($allMembers as $m) {
                if (
                    stripos((string) ($m['name'] ?? ''), $memberKeyword) !== false
                    || stripos((string) ($m['job'] ?? ''), $memberKeyword) !== false
                ) {
                    $filteredMembers[] = $m;
                }
            }
            $targetMembers = $filteredMembers;
        } else {
            $targetMembers = $allMembers;
        }

        $pager = service('pager');
        $currentPage = $pager->getCurrentPage('members');
        $perPage = 10;
        $totalMembers = count($targetMembers);
        $pager->store('members', $currentPage, $perPage, $totalMembers);

        $metrics['members'] = array_slice($targetMembers, ($currentPage - 1) * $perPage, $perPage);

        return view('team_performance/index', [
            'title'                 => 'Kinerja Tim',
            'metrics'               => $metrics,
            'pager'                 => $pager,
            'totalMembers'          => $totalRegisteredMembers,
            'totalFilteredMembers'  => $totalMembers,
            'memberKeyword'         => $memberKeyword,
            'selectedStartDate'     => $range['start_date'] ?? $startDate,
            'selectedEndDate'       => $range['end_date'] ?? $endDate,
        ]);
    }
}
