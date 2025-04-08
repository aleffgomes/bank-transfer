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

    public function isMerchant(): bool
    {
        return $this->attributes['type_name'] === 'merchant';
    }

    public function isCommonUser(): bool
    {
        return $this->attributes['type_name'] === 'user';
    }

    public function canSendMoney(): bool
    {
        return $this->isCommonUser();
    }
} 