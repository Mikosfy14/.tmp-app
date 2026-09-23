<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
    protected NotificationModel $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function read($id)
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0 || !$this->notificationModel->markAsReadForUser((int) $id, $userId)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function readAll()
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Sesi pengguna tidak valid.',
            ]);
        }

        $this->notificationModel->markAllAsReadForUser($userId);

        return $this->response->setJSON(['success' => true]);
    }
}
