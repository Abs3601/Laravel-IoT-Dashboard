<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupOldEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-old-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete IoT events older than 6 months';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = now()->subMonths(6);
        
        $count = \App\Models\IoTEvent::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted $count events older than $cutoff.");
    }
}
