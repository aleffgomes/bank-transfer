<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class ProcessNotificationQueue extends BaseCommand
{
    protected $group = 'Custom';
    protected $name = 'queue:process';
    protected $description = 'Process the notification queue from RabbitMQ.';
    protected $usage = 'queue:process [options]';
    protected $options = [
        '--timeout' => 'Maximum time in seconds to process messages (default: 30)',
    ];

    public function run(array $params)
    {
        $timeout = $params['timeout'] ?? 30;
        
        CLI::write('Starting notification queue processing...', 'yellow');
        CLI::write("Processing will run for up to {$timeout} seconds", 'yellow');
        
        try {
            $notificationService = Services::notificationService();
            $startTime = time();
            
            $processed = $notificationService->retryFailedNotifications($timeout);
            
            $elapsedTime = time() - $startTime;
            
            if ($processed > 0) {
                CLI::write("Processed {$processed} notification(s) in {$elapsedTime} seconds.", 'green');
            } else {
                CLI::write('No notifications to process.', 'blue');
            }
        } catch (\Exception $e) {
            CLI::error('Error processing notification queue: ' . $e->getMessage());
            return EXIT_ERROR;
        }
        
        CLI::write('Notification queue processing completed.', 'green');
        return EXIT_SUCCESS;
    }
}
