<?php

namespace Tests\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\AuthorizationService;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response;

class AuthorizationServiceTest extends CIUnitTestCase
{
    protected $authorizationService;
    protected $curlRequest;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->curlRequest = $this->createMock(CURLRequest::class);
        $this->authorizationService = new AuthorizationService($this->curlRequest);
    }
    
    public function testCheckAuthorizationSuccess()
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')
            ->willReturn(200);
        
        $this->curlRequest->method('request')
            ->with('GET', 'https://util.devi.tools/api/v2/authorize')
            ->willReturn($response);
        
        $result = $this->authorizationService->checkAuthorization();
        
        $this->assertTrue($result);
    }
    
    public function testCheckAuthorizationFailure()
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')
            ->willReturn(403);
        
        $this->curlRequest->method('request')
            ->with('GET', 'https://util.devi.tools/api/v2/authorize')
            ->willReturn($response);
        
        $result = $this->authorizationService->checkAuthorization();
        
        $this->assertFalse($result);
    }
    
    public function testCheckAuthorizationException()
    {
        $this->curlRequest->method('request')
            ->with('GET', 'https://util.devi.tools/api/v2/authorize')
            ->willThrowException(new \Exception('Connection failed'));
        
        $result = $this->authorizationService->checkAuthorization();
        
        $this->assertFalse($result);
    }
} 