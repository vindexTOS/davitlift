<?php
namespace App\Services;

use Illuminate\Support\Facades\App;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;

class MqttConnectionService {

    private static $instance = null;

    private $server;
    private $port;
    private $clientId;
    private $username;
    private $password;
    private $mqtt_version;
    private $mqtt;
    protected $logManager;

    // Constructor is private
    private function __construct() {

        $this->server = '3.71.18.216';
        $this->port = '1883';
        $this->clientId = rand(5, 15);
        $this->username = 'username';
        $this->password = '12345678';
        $this->mqtt_version = MqttClient::MQTT_3_1_1;
    }

    // Public static method to get the instance
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new MqttConnectionService();
        }
        return self::$instance;
    }

    public function connect() {
        if (is_null($this->mqtt)) {
            $connectionSettings = (new ConnectionSettings)
                ->setUsername($this->username)
                ->setPassword($this->password)
                ->setKeepAliveInterval(60)
                ->setLastWillTopic(null)
                ->setMaxReconnectAttempts(5)
                ->setLastWillMessage('client disconnect')
                ->setReconnectAutomatically(true)
                ->setLastWillQualityOfService(1);
            $this->mqtt = new MqttClient($this->server, $this->port, $this->clientId, $this->mqtt_version,$this->logManager);
            $this->mqtt->connect($connectionSettings, false);
        }
        return $this->mqtt;
    }

}
