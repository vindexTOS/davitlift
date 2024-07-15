<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class CurrentTime extends Command
{
    protected $signature = 'current:time';
    protected $description = 'Display current time';
    
    public function handle()
    {
        $currentTime = Carbon::now();
        $this->info('Current time: ' . $currentTime->toDateTimeString());
    }
}