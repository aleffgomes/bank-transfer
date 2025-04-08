<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;
use Exception;

class ProcessNotificationQueue extends BaseCommand
{
    protected $group = 'Custom';
    protected $name = 'queue:process';
    protected $description = 'Process the notification queue from RabbitMQ continuously.';
    protected $usage = 'queue:process [options]';
    protected $options = [
        '--sleep' => 'Sleep time in seconds between processing attempts when queue is empty (default: 5)',
    ];

    private bool $shouldContinue = true;

    public function run(array $params)
    {
        $sleepTime = $params['sleep'] ?? 5;
        
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
        
        // Configurar logging
        $logMessage = 'Starting notification queue processing...';
        CLI::write($logMessage, 'yellow');
        log_message('info', $logMessage);
        
        $logMessage = "Sleep time between attempts: {$sleepTime} seconds";
        CLI::write($logMessage, 'yellow');
        log_message('info', $logMessage);
        
        try {
            $notificationService = Services::notificationService();
            
            while ($this->shouldContinue) {
                try {
                    $processed = $notificationService->processNotifications();
                    
                    if ($processed > 0) {
                        $logMessage = date('[Y-m-d H:i:s]') . " Processed {$processed} notification(s).";
                        CLI::write($logMessage, 'green');
                        log_message('info', $logMessage);
                    }
                    
                    $retried = $notificationService->retryFailedNotifications();
                    
                    if ($retried > 0) {
                        $logMessage = date('[Y-m-d H:i:s]') . " Retried {$retried} failed notification(s).";
                        CLI::write($logMessage, 'yellow');
                        log_message('info', $logMessage);
                    }
                    
                    if ($processed === 0 && $retried === 0) {
                        $logMessage = date('[Y-m-d H:i:s]') . " No notifications to process. Waiting {$sleepTime} seconds...";
                        CLI::write($logMessage, 'blue');
                        log_message('info', $logMessage);
                        sleep($sleepTime);
                    }
                    
                } catch (Exception $e) {
                    log_message('error', 'Error processing notifications: ' . $e->getMessage());
                    CLI::error(date('[Y-m-d H:i:s]') . ' Error processing notifications: ' . $e->getMessage());
                    
                    sleep($sleepTime);
                }
            }
        } catch (Exception $e) {
            $logMessage = 'Fatal error in notification processor: ' . $e->getMessage();
            CLI::error($logMessage);
            log_message('error', $logMessage);
            return EXIT_ERROR;
        }
        
        $logMessage = 'Notification queue processing stopped.';
        CLI::write($logMessage, 'yellow');
        log_message('info', $logMessage);
        return EXIT_SUCCESS;
    }

    private function shutdown()
    {
        $logMessage = 'Shutting down notification processor...';
        CLI::write($logMessage, 'yellow');
        log_message('info', $logMessage);
        $this->shouldContinue = false;
    }
}
