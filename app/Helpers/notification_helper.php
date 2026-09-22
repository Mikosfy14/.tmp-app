<?php

if (!function_exists('get_user_feedback_notifications')) {
    /**
     * Mengambil daftar notifikasi feedback/arahan logbook untuk user aktif (Preview/Mock data).
     *
     * @param int|null $userId ID User (default: session user_id)
     * @param int|null $roleId ID Role (default: session role_id)
     * @return array<int, array{id: int, logbook_id: int, project_id: int, project_name: string, sender_name: string, sender_role: string, message: string, created_at: string}>
     */
    function get_user_feedback_notifications(?int $userId = null, ?int $roleId = null): array
    {
        $userId = $userId ?? (int) session()->get('user_id');
        $roleId = $roleId ?? (int) session()->get('role_id');
        if ($userId <= 0) {
            return [];
        }

        // Mock preview notifications for logbook feedback / arahan
        return [
            [
                'id'           => 1,
                'logbook_id'   => 1,
                'project_id'   => 1,
                'project_name' => 'Sistem Informasi Manajemen Aset',
                'sender_name'  => 'Ahmad Fauzi, M.Kom',
                'sender_role'  => 'Kepala Departemen',
                'message'      => 'Mohon pastikan integrasi API payment gateway telah dilakukan stress test sebelum promote.',
                'created_at'   => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
            [
                'id'           => 2,
                'logbook_id'   => 2,
                'project_id'   => 1,
                'project_name' => 'Sistem Informasi Manajemen Aset',
                'sender_name'  => 'Rian Pratama',
                'sender_role'  => 'Staff',
                'message'      => 'Terdapat blocker query timeout pada modul laporan rekonsiliasi bulanan.',
                'created_at'   => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
        ];
    }
}
