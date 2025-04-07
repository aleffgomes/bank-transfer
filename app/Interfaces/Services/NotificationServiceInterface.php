<?php

namespace App\Interfaces\Services;
use CodeIgniter\HTTP\ResponseInterface;

interface NotificationServiceInterface
{
    /**
     * Sends a notification to the user
     */
    public function sendNotification(int $userId, string $message, bool $addToQueue = true): bool;
    
    /**
     * Adds a notification to the queue
     */
    public function addToQueue(array $data): void;
    
    /**
     * Process notifications from the queue without timeout
     * 
     * @return int Number of processed messages
     */
    public function processNotifications(): int;
    
    /**
     * Retries failed notifications
     * 
     * @param int $timeout Maximum time in seconds to process messages (default: 10)
     * @return int Number of processed messages
     */
    public function retryFailedNotifications(int $timeout = 10): int;
}
