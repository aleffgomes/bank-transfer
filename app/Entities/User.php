<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $attributes = [
        'id_user' => null,
        'name' => null,
        'email' => null,
        'document' => null,
        'password' => null,
        'type_id' => null,
        'type_name' => null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $casts = [
        'id_user' => 'integer',
        'type_id' => 'integer',
    ];

    /**
     * Checks if user is a merchant
     *
     * @return bool
     */
    public function isMerchant(): bool
    {
        return $this->attributes['type_name'] === 'merchant';
    }

    /**
     * Checks if user is a common user
     *
     * @return bool
     */
    public function isCommonUser(): bool
    {
        return $this->attributes['type_name'] === 'user';
    }

    /**
     * Checks if this user can send money
     * (only common users can send money)
     *
     * @return bool
     */
    public function canSendMoney(): bool
    {
        return $this->isCommonUser();
    }
} 