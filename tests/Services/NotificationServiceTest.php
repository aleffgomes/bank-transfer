<?php

namespace Tests\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\NotificationService;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class NotificationServiceTest extends CIUnitTestCase
{
    protected $notificationService;
    protected $curlRequest;
    protected $connectionMock;
    protected $channelMock;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->curlRequest = $this->createMock(CURLRequest::class);
        
        $this->connectionMock = $this->getMockBuilder(AMQPStreamConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->channelMock = $this->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->connectionMock->method('channel')
            ->willReturn($this->channelMock);
            
        $this->connectionMock->method('isConnected')
            ->willReturn(true);
            
        $this->channelMock->method('is_open')
            ->willReturn(true);
        
        $this->channelMock->expects($this->any())
            ->method('queue_declare')
            ->willReturn([true]);
            
        $this->channelMock->expects($this->any())
            ->method('basic_qos');
            
        $this->notificationService = new class($this->curlRequest) extends NotificationService {
            public function setConnectionAndChannel($connection, $channel) {
                $this->connection = $connection;
                $this->channel = $channel;
            }
        };
        
        $this->notificationService->setConnectionAndChannel($this->connectionMock, $this->channelMock);
    }
    
    public function testSendNotificationSuccess()
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')
            ->willReturn(204);
        
        $this->curlRequest->method('request')
            ->with(
                'POST', 
                'https://util.devi.tools/api/v1/notify',
                $this->anything()
            )
            ->willReturn($response);
        
        $result = $this->notificationService->sendNotification(1, 'Test message');
        
        $this->assertTrue($result);
    }
    
    public function testSendNotificationFailure()
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')
            ->willReturn(500);
        
        $this->curlRequest->method('request')
            ->with(
                'POST', 
                'https://util.devi.tools/api/v1/notify',
                $this->anything()
            )
            ->willThrowException(new \Exception('API call failed'));
            
        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function ($message) {
                    return $message instanceof AMQPMessage;
                }),
                '',
                'notification_queue'
            );
        
        $result = $this->notificationService->sendNotification(1, 'Test message');
        
        $this->assertFalse($result);
    }
    
    public function testSendNotificationException()
    {
        $this->curlRequest->method('request')
            ->with(
                'POST', 
                'https://util.devi.tools/api/v1/notify',
                $this->anything()
            )
            ->willThrowException(new \Exception('Connection failed'));
            
        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function ($message) {
                    return $message instanceof AMQPMessage;
                }),
                '',
                'notification_queue'
            );
        
        $result = $this->notificationService->sendNotification(1, 'Test message');
        
        $this->assertFalse($result);
    }
    
    public function testAddToQueue()
    {
        $data = ['user_id' => 1, 'message' => 'Test message'];
        
        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function ($message) {
                    return $message instanceof AMQPMessage;
                }),
                '',
                'notification_queue'
            );
        
        $this->notificationService->addToQueue($data);
    }
} 