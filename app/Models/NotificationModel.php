<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'user_notifications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'recipient_user_id',
        'actor_user_id',
        'project_id',
        'logbook_id',
        'notification_type',
        'title',
        'message',
        'event_key',
        'read_at',
    ];

    public function getRecentForUser(int $userId, int $limit = 50): array
    {
        $this->cleanupForRecipient($userId);

        return $this->select('
                user_notifications.*,
                actor.name as actor_name,
                projects.name as project_name
            ')
            ->join('users as actor', 'actor.id = user_notifications.actor_user_id', 'left')
            ->join('projects', 'projects.id = user_notifications.project_id', 'left')
            ->where('user_notifications.recipient_user_id', $userId)
            ->where('user_notifications.read_at IS NULL', null, false)
            ->orderBy('user_notifications.created_at', 'DESC')
            ->orderBy('user_notifications.id', 'DESC')
            ->findAll(max(1, min($limit, 100)));
    }

    public function countUnreadForUser(int $userId): int
    {
        $this->cleanupForRecipient($userId);

        return $this->where('recipient_user_id', $userId)
            ->where('read_at IS NULL', null, false)
            ->countAllResults();
    }

    public function markAsReadForUser(int $notificationId, int $userId): bool
    {
        return $this->where('id', $notificationId)
            ->where('recipient_user_id', $userId)
            ->delete();
    }

    public function markAllAsReadForUser(int $userId): bool
    {
        return $this->where('recipient_user_id', $userId)
            ->delete();
    }

    public function createLogbookSubmittedNotifications(int $projectId, int $logbookId, int $actorUserId, string $projectName): void
    {
        $recipients = $this->db->table('users')
            ->select('users.id')
            ->join('roles', 'roles.id = users.role_id', 'inner')
            ->where('users.is_active', 1)
            ->groupStart()
                ->where('users.role_id', 1)
                ->orWhere('roles.role_name', 'Kepala Departemen')
            ->groupEnd()
            ->where('users.id !=', $actorUserId)
            ->get()
            ->getResultArray();

        foreach ($recipients as $recipient) {
            $this->insertIfMissing([
                'recipient_user_id' => (int) $recipient['id'],
                'actor_user_id' => $actorUserId,
                'project_id' => $projectId,
                'logbook_id' => $logbookId,
                'notification_type' => 'logbook_submitted',
                'title' => 'Laporan Logbook Baru',
                'message' => 'Laporan teknis baru tersedia pada project ' . $projectName . '.',
                'event_key' => 'logbook_submitted:' . $logbookId . ':user:' . (int) $recipient['id'],
            ]);
            $this->cleanupForRecipient((int) $recipient['id']);
        }
    }

    public function createGuidanceNotification(
        int $recipientUserId,
        int $actorUserId,
        int $projectId,
        int $logbookId,
        string $projectName,
        string $messageHash,
        string $message
    ): void {
        $message = mb_substr(trim($message), 0, 800);

        $this->insertIfMissing([
            'recipient_user_id' => $recipientUserId,
            'actor_user_id' => $actorUserId,
            'project_id' => $projectId,
            'logbook_id' => $logbookId,
            'notification_type' => 'logbook_guidance',
            'title' => 'Arahan Kepala Departemen',
            'message' => mb_substr('Ada arahan baru pada Logbook project ' . $projectName . ': ' . $message, 0, 1000),
            'event_key' => 'logbook_guidance:' . $logbookId . ':' . $messageHash,
        ]);
        $this->cleanupForRecipient($recipientUserId);
    }

    public function cleanupForRecipient(int $userId, int $maxUnread = 100, int $retentionDays = 90): void
    {
        if ($userId <= 0) {
            return;
        }

        $this->where('recipient_user_id', $userId)
            ->where('read_at IS NOT NULL', null, false)
            ->delete();

        $this->db->query(
            'DELETE FROM user_notifications
             WHERE recipient_user_id = ?
               AND read_at IS NULL
               AND created_at < DATEADD(DAY, ?, GETDATE())',
            [$userId, -abs($retentionDays)]
        );

        $unreadIds = $this->select('id')
            ->where('recipient_user_id', $userId)
            ->where('read_at IS NULL', null, false)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($maxUnread + 1);

        if (count($unreadIds) <= $maxUnread) {
            return;
        }

        $idsToDelete = array_column(array_slice($unreadIds, $maxUnread), 'id');
        if (!empty($idsToDelete)) {
            $this->whereIn('id', $idsToDelete)->delete();
        }
    }

    private function insertIfMissing(array $data): void
    {
        if ($this->where('event_key', $data['event_key'])->first()) {
            return;
        }

        $this->insert($data, false);
    }
}
