<?php

namespace App\Services;

use App\Interfaces\Services\NotificationServiceInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class NotificationService implements NotificationServiceInterface
{
    const QUEUE_NAME = 'notification_queue';
    const MAX_ATTEMPTS = 5;
    protected $client;
    protected $connection;
    protected $channel;

    public function __construct($client)
    {
        $this->client = $client;
        $this->setupRabbitMqConnection();
    }

    private function setupRabbitMqConnection(): void
    {
        try {
            $this->connection = new AMQPStreamConnection(
                getenv('rabbitmq.host'),
                getenv('rabbitmq.port'),
                getenv('rabbitmq.user'),
                getenv('rabbitmq.password'),
                getenv('rabbitmq.vhost')
            );
            
            $this->channel = $this->connection->channel();
            
            $this->channel->queue_declare(
                self::QUEUE_NAME,   
                false,              
                true,              
                false,              
                false              
            );
            
            $this->channel->basic_qos(
                null,               
                1,                  
                null                
            );
        } catch (\Exception $e) {
            log_message('error', 'Error connecting to RabbitMQ: ' . $e->getMessage());
        }
    }
    
    public function __destruct()
    {
        if (isset($this->channel) && $this->channel->is_open()) {
            $this->channel->close();
        }
        
        if (isset($this->connection) && $this->connection->isConnected()) {
            $this->connection->close();
        }
    }

    public function sendNotification(int $userId, string $message, bool $addToQueue = true): bool
    {
        $data = [
            'user_id' => $userId,
            'message' => $message,
        ];

        try {
            $response = $this->client->request('POST', 'https://util.devi.tools/api/v1/notify', [
                'json' => $data,
            ]);

            return $response->getStatusCode() === 204;
        } catch (\Exception $e) {
            if ($addToQueue) $this->addToQueue($data);
            return false;
        }
    }

    public function addToQueue(array $data): void
    {
        if (!isset($data['attempts'])) {
            $data['attempts'] = 1;
        } else {
            $data['attempts'] += 1;
        }

        try {
            if (!$this->channel || !$this->channel->is_open()) {
                $this->setupRabbitMqConnection();
            }
            
            $msg = new AMQPMessage(
                json_encode($data),
                [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]
            );
            
            $this->channel->basic_publish(
                $msg,               
                '',                 
                self::QUEUE_NAME    
            );
            
            log_message('info', 'Notification added to queue: ' . json_encode($data));
        } catch (\Exception $e) {
            log_message('error', 'Error adding notification to queue: ' . $e->getMessage());
        }
    }

    public function processNotifications(): int
    {
        $processedCount = 0;
        
        try {
            if (!$this->channel || !$this->channel->is_open()) {
                $this->setupRabbitMqConnection();
            }
            
            $callback = function (AMQPMessage $msg) use (&$processedCount) {
                $data = json_decode($msg->body, true);
                $processedCount++;
                
                if ($data['attempts'] > self::MAX_ATTEMPTS) {
                    $msg->nack(false);
                    log_message('warning', 'Notification discarded after ' . self::MAX_ATTEMPTS . ' attempts: ' . $msg->body);
                    return;
                }
                
                if ($this->sendNotification($data['user_id'], $data['message'], false)) {
                    $msg->ack();
                    log_message('info', 'Notification processed successfully: ' . $msg->body);
                } else {
                    $data['attempts'] += 1;
                    $this->addToQueue($data);
                    
                    $msg->ack();
                    log_message('info', 'Notification failed, re-queued: ' . json_encode($data));
                }
            };
            
            $this->channel->basic_consume(
                self::QUEUE_NAME,       
                '',                     
                false,                  
                false,                  
                false,                  
                false,                  
                $callback               
            );
            
            // Process one message and return
            if (count($this->channel->callbacks)) {
                $this->channel->wait(null, true, 1);
            }
            
            return $processedCount;
        } catch (\Exception $e) {
            log_message('error', 'Error processing notification queue: ' . $e->getMessage());
            return $processedCount;
        }
    }

    public function retryFailedNotifications(int $timeout = 10): int
    {
        $processedCount = 0;
        
        try {
            if (!$this->channel || !$this->channel->is_open()) {
                $this->setupRabbitMqConnection();
            }
            
            $callback = function (AMQPMessage $msg) use (&$processedCount) {
                $data = json_decode($msg->body, true);
                $processedCount++;
                
                if ($data['attempts'] > self::MAX_ATTEMPTS) {
                    $msg->nack(false);
                    log_message('warning', 'Notification discarded after ' . self::MAX_ATTEMPTS . ' attempts: ' . $msg->body);
                    return;
                }
                
                if ($this->sendNotification($data['user_id'], $data['message'], false)) {
                    $msg->ack();
                    log_message('info', 'Notification processed successfully: ' . $msg->body);
                } else {
                    $data['attempts'] += 1;
                    $this->addToQueue($data);
                    
                    $msg->ack();
                    log_message('info', 'Notification failed, re-queued: ' . json_encode($data));
                }
            };
            
            $this->channel->basic_consume(
                self::QUEUE_NAME,       
                '',                     
                false,                  
                false,                  
                false,                  
                false,                  
                $callback               
            );
            
            $timeLimit = time() + $timeout;
            while (time() < $timeLimit && count($this->channel->callbacks)) {
                $this->channel->wait(null, true, 1); 
            }
            
            return $processedCount;
        } catch (\Exception $e) {
            log_message('error', 'Error processing notification queue: ' . $e->getMessage());
            return $processedCount;
        }
    }
}
