<?php

namespace App\Interfaces\Services;

interface AuthorizationServiceInterface
{
    public function checkAuthorization(): bool;
}
