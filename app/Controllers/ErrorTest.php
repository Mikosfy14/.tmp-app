<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class ErrorTest extends BaseController
{
    /**
     * Preview 403 Forbidden Error Page
     */
    public function error403()
    {
        return $this->response
            ->setStatusCode(403)
            ->setBody(view('errors/html/error_403'));
    }

    /**
     * Preview 404 Not Found Error Page
     */
    public function error404()
    {
        return $this->response
            ->setStatusCode(404)
            ->setBody(view('errors/html/error_404', [
                'message' => 'Periksa kembali alamat URL atau kembali ke dashboard utama.'
            ]));
    }

    /**
     * Preview 500 Internal Server Error Page
     */
    public function error500()
    {
        return $this->response
            ->setStatusCode(500)
            ->setBody(view('errors/html/error_500'));
    }
}
