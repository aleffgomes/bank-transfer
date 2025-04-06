<?php

namespace App\Interfaces\Models;

interface UserModelInterface
{
    public function getUserById(int $userId);
}
