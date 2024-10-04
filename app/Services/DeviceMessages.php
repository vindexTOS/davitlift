<?php 

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;





trait DeviceMessages { 


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

    public function generateHexPayload($command, $payload)
    {
        return [
            'command' => $command,
            'payload' => $payload,
        ];
    }



  public function SendingDeviceSubscriptionDate($device_id, $subscription, $data ){
    $payload = $this->generateHexPayload(2, [
        [
            'type' => 'timestamp',
            'value' => Carbon::parse($subscription)
                ->timestamp,
        ],
        [
            'type' => 'string',
            'value' => $data['payload'],
        ],
        [
            'type' => 'number',
            'value' => 0,
        ],
    ]);

    $this->publishMessage($device_id, $payload);
     
  }

    public function ServiceNotAvalableMessage($device_id){
        $payload = $this->generateHexPayload(6, [
            [
                'type' => 'string',
                'value' => 'servisi',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'droebiT',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'SezRudulia',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
        ]);
        $this->publishMessage($device_id, $payload);
    }



    public function DeviceNotInSystem($device_id){
        $payload = $this->generateHexPayload(6, [
            [
                'type' => 'string',
                'value' => 'mowyobiloba',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'araa',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'sistemaSi',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
        ]);
        $this->publishMessage($device_id, $payload);
    }

     
    public function PhoneNumberNotFound($device_id){
        $payload = $this->generateHexPayload(6, [
            [
                'type' => 'string',
                'value' => 'nomeri',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'ver',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'moiZebna',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
        ]);
        $this->publishMessage($device_id, $payload);

    }


    public function noMoney($device_id)
    {
        $payload = $this->generateHexPayload(6, [
            [
                'type' => 'string',
                'value' => 'araa',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'sakmarisi',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'Tanxa',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
        ]);
        $this->publishMessage($device_id, $payload);
    }

}