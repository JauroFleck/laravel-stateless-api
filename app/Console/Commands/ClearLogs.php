<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the file laravel.log';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            file_put_contents($logFile, ''); // Clears the file contents
            $this->info('Logs have been cleared.');
        } else {
            $this->error('Log file does not exist.');
        }
    }
}
