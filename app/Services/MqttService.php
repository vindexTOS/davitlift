<?php

// app/Services/MqttService.php

namespace App\Services;

use PDOException;
use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use RuntimeException;
use App\Models\Device;
use App\Models\ErrorLogs;
use App\Models\DeviceEarn;
use App\Models\DeviceUser;
use App\Models\DeviceError;
use App\Models\ElevatorUse;
use App\Models\Transaction;
use App\Models\LastUserAmount;
use App\Models\UpdatingDevice;
use PhpMqtt\Client\MqttClient;
use App\Models\UnregisteredDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isNull;
use function PHPUnit\Framework\exactly;
use Illuminate\Support\Facades\Storage;

class MqttService
{
    public $mqtt;
    
    public function __construct()
    {
        $mqttService = app(MqttConnectionService::class);
        $this->mqtt = $mqttService->connect();
    }
    
    public function run()
    {
 
    }}                                   
                                             
                                                