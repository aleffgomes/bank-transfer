<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

class CheckAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authorizationService = \Config\Services::authorizationService();

        if(!$authorizationService->checkAuthorization()) {
            $response = Services::response();
            $response->setStatusCode(401);
            $response->setHeader('Content-Type', 'application/json');
            $response->setBody(json_encode(['error' => 'Unauthorized', 'status' => 401]));
            return $response;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
