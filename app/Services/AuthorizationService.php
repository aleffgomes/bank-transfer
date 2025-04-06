<?php

namespace App\Services;

use App\Interfaces\Services\AuthorizationServiceInterface;
use CodeIgniter\HTTP\CURLRequest;

class AuthorizationService implements AuthorizationServiceInterface
{
    protected $client;

    public function __construct(CURLRequest $client)
    {
        $this->client = $client;
    }

    public function checkAuthorization(): bool
    {
        try {
            $response = $this->client->request('GET', 'https://util.devi.tools/api/v2/authorize');
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
