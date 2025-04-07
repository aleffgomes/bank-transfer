<?php

namespace Tests\Entities;

use CodeIgniter\Test\CIUnitTestCase;
use App\Entities\User;

class UserTest extends CIUnitTestCase
{
    public function testIsMerchant()
    {
        // Teste para usuário comum
        $commonUser = new User([
            'type_name' => 'user'
        ]);
        $this->assertFalse($commonUser->isMerchant());
        
        // Teste para lojista
        $merchantUser = new User([
            'type_name' => 'merchant'
        ]);
        $this->assertTrue($merchantUser->isMerchant());
    }
    
    public function testIsCommonUser()
    {
        // Teste para usuário comum
        $commonUser = new User([
            'type_name' => 'user'
        ]);
        $this->assertTrue($commonUser->isCommonUser());
        
        // Teste para lojista
        $merchantUser = new User([
            'type_name' => 'merchant'
        ]);
        $this->assertFalse($merchantUser->isCommonUser());
    }
    
    public function testCanSendMoney()
    {
        // Teste para usuário comum
        $commonUser = new User([
            'type_name' => 'user'
        ]);
        $this->assertTrue($commonUser->canSendMoney());
        
        // Teste para lojista
        $merchantUser = new User([
            'type_name' => 'merchant'
        ]);
        $this->assertFalse($merchantUser->canSendMoney());
    }
} 