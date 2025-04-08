<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class PingController extends ResourceController
{
    public function ping(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->respond([
            'message' => 'pong',
            'method' => $this->request->getMethod(),
        ])->setStatusCode(200);
    }
}
