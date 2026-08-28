<?php

namespace App\Controllers;

use App\Libraries\DashboardMetrics;

class TeamPerformance extends BaseController
{
    public function index()
    {
        if (session()->get('role_name') !== 'Kepala Departemen') {
            return redirect()->to('/dashboard')->with('error', 'Akses hanya tersedia untuk Kepala Departemen.');
        }

        $start = trim((string) $this->request->getGet('filter_start'));
        $end = trim((string) $this->request->getGet('filter_end'));
        $valid = static function (string $date): bool { $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date); return $parsed !== false && $parsed->format('Y-m-d') === $date; };
        $range = $valid($start) && $valid($end) ? ['start_date' => min($start, $end), 'end_date' => max($start, $end)] : null;
        return view('team_performance/index', ['title' => 'Kinerja Tim', 'metrics' => (new DashboardMetrics())->team($range), 'selectedStartDate' => $range['start_date'] ?? '', 'selectedEndDate' => $range['end_date'] ?? '']);
    }
}
