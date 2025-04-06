<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class PingController extends ResourceController
{
    /**
     * Ping endpoint.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function ping(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->respond([
            'message' => 'pong',
            'method' => $this->request->getMethod(),
        ])->setStatusCode(200);
    }
}
