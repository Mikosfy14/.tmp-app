<?php

if (!function_exists('get_user_feedback_notifications')) {
    /**
     * Mengambil daftar notifikasi Logbook untuk user aktif.
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

        try {
            $notifications = (new \App\Models\NotificationModel())->getRecentForUser($userId);
            return array_map(static function (array $notification): array {
                return [
                    'id' => (int) $notification['id'],
                    'logbook_id' => (int) $notification['logbook_id'],
                    'project_id' => (int) $notification['project_id'],
                    'project_name' => (string) ($notification['project_name'] ?? 'Project'),
                    'sender_name' => (string) ($notification['actor_name'] ?? 'User'),
                    'sender_role' => $notification['notification_type'] === 'logbook_guidance'
                        ? 'Kepala Departemen'
                        : 'Laporan Logbook',
                    'message' => (string) $notification['message'],
                    'created_at' => (string) $notification['created_at'],
                    'notification_type' => (string) $notification['notification_type'],
                    'read_at' => $notification['read_at'],
                ];
            }, $notifications);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengambil notifikasi Logbook: {message}', ['message' => $e->getMessage()]);
            return [];
        }
    }
}

if (!function_exists('get_user_feedback_unread_count')) {
    function get_user_feedback_unread_count(?int $userId = null): int
    {
        $userId = $userId ?? (int) session()->get('user_id');
        if ($userId <= 0) {
            return 0;
        }

        try {
            return (new \App\Models\NotificationModel())->countUnreadForUser($userId);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menghitung notifikasi Logbook belum dibaca: {message}', ['message' => $e->getMessage()]);
            return 0;
        }
    }
}
