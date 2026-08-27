<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    private const IDLE_TIMEOUT = 900;

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            // Redirect to login page if not logged in
            return redirect()->to('/login');
        }

        $lastActivity = (int) $session->get('last_activity');
        if ($lastActivity > 0 && (time() - $lastActivity) >= self::IDLE_TIMEOUT) {
            $session->destroy();

            return redirect()->to('/login')->with('error', 'Sesi Anda berakhir karena tidak ada aktivitas selama 15 menit. Silakan login kembali.');
        }

        $session->set('last_activity', time());
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after the request
    }
}
