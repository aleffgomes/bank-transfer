<?php

namespace App\Interfaces\Models;

use App\Entities\User;

interface UserModelInterface
{
    /**
     * Get user by ID.
     *
     * @param int $userId The user ID.
     * @return User|null The user data or null if not found.
     */
    public function getUserById(int $userId): ?User;
}
