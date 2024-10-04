<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

trait  MQTTServiceProvider  
{
    public function publishMessage($device_id, $payload)
    {
        $data = [
            'device_id' => $device_id,
            'payload' => $payload,
        ];
        $queryParams = http_build_query($data);
        $response = Http::get(
            'http://localhost:3000/mqtt/general?' . $queryParams
        );
        return $response->json(['data' => ['dasd']]);
    }


}
