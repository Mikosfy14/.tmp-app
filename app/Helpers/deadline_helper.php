<?php

if (!function_exists('get_deadline_status')) {
    /**
     * Menghitung status deadline project berdasarkan end_date dan status penyelesaian.
     *
     * @param string|null $endDate Tanggal akhir / deadline project (format: Y-m-d)
     * @param bool $completed Status apakah project sudah selesai
     * @return array{label: string, class: string, badge_class: string, days_left: int|null}
     */
    function get_deadline_status(?string $endDate, bool $completed = false): array
    {
        if ($completed || empty($endDate)) {
            return [
                'label'       => 'On Track',
                'class'       => 'success',
                'badge_class' => 'bg-success',
                'days_left'   => null,
            ];
        }

        try {
            $today = new DateTimeImmutable(date('Y-m-d'));
            $targetDate = new DateTimeImmutable(date('Y-m-d', strtotime($endDate)));
            $daysLeft = (int) $today->diff($targetDate)->format('%r%a');
        } catch (\Exception $e) {
            return [
                'label'       => 'On Track',
                'class'       => 'success',
                'badge_class' => 'bg-success',
                'days_left'   => null,
            ];
        }

        if ($daysLeft < 0) {
            return [
                'label'       => 'Overdue',
                'class'       => 'danger',
                'badge_class' => 'bg-danger',
                'days_left'   => $daysLeft,
            ];
        }

        if ($daysLeft <= 1) {
            return [
                'label'       => 'Critical',
                'class'       => 'danger',
                'badge_class' => 'bg-danger',
                'days_left'   => $daysLeft,
            ];
        }

        if ($daysLeft < 3) {
            return [
                'label'       => 'Urgent',
                'class'       => 'danger',
                'badge_class' => 'bg-danger',
                'days_left'   => $daysLeft,
            ];
        }

        if ($daysLeft < 7) {
            return [
                'label'       => 'Risk',
                'class'       => 'warning',
                'badge_class' => 'bg-warning text-dark',
                'days_left'   => $daysLeft,
            ];
        }

        return [
            'label'       => 'On Track',
            'class'       => 'success',
            'badge_class' => 'bg-success',
            'days_left'   => $daysLeft,
        ];
    }
}

if (!function_exists('is_project_completed')) {
    /**
     * Memeriksa apakah suatu project berstatus selesai.
     *
     * @param array $project Data row project
     * @return bool
     */
    function is_project_completed(array $project): bool
    {
        $status = strtolower((string) ($project['status'] ?? ''));
        return !empty($project['promote_date'])
            || str_contains($status, 'deployment')
            || str_contains($status, 'complete')
            || str_contains($status, 'selesai')
            || str_contains($status, 'done');
    }
}
