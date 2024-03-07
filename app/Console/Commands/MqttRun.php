<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;

class MqttRun extends Command
{
    protected $signature = 'mqtt:run';
    protected $description = 'Run the MQTT Service';

    public function handle()
    {
        $this->info('Running MQTT Service...');

        $service = new MqttService();
        $service->run();

        $this->info('MQTT Service has finished running.');
    }
}
