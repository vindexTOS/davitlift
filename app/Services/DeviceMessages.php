<?php
namespace   App\Services;



use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;





trait DeviceMessages
{


    public function publishMessage($device_id, $payload)
    {

        Log::info("medevice ", ["op"=>"DEVICE"]);

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



    public function SendingDeviceSubscriptionDate($device_id, $subscription, $data)
    {
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


    public function ReturnSubscriptionTypeToDevice($userDevice, $data, $device)
    {
 
        $payload = $this->generateHexPayload(4, [
            [
                'type' => 'timestamp',
                'value' => Carbon::parse($userDevice->subscription)
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
        $this->publishMessage($device->dev_id, $payload);
    }

    public function getDeviceCode($device_id, $code){
        $payload = $this->generateHexPayload(6, [
            [
                'type' => 'string',
                'value' => 'Tqveni',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'kodia',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => $code,
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
        ]);
        $this->publishMessage($device_id, $payload);
    }


    public function WrongeCode($device_id)
    {
        $payload = $this->generateHexPayload(6, [
            [
                'type' => 'string',
                'value' => 'araswori',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'kodi',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => '',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
        ]);
        $this->publishMessage($device_id, $payload);
    }


    public function ServiceNotAvalableMessage($device_id)
    {
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

    public function DeviceSTR_PAD_0($user, $device)
    {


        $payload = $this->generateHexPayload(1, [
            [
                'type' => 'string',
                'value' => str_pad($user->id, 6, '0', STR_PAD_LEFT),
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'number16',
                'value' => $user->balance - $device->tariff_amount,
            ],
        ]);
        $this->publishMessage($device->dev_id, $payload);

        //second veriosn 

         // $payload = $this->generateHexPayload(1, [
                //     [
                //         'type' => 'string',
                //         'value' => str_pad($user->id, 6, '0', STR_PAD_LEFT),
                //     ],
                //     [
                //         'type' => 'number',
                //         'value' => 0,
                //     ],
                //     [
                //         'type' => 'number16',
                //         'value' => $user->balance - $user->freezed_balance,
                //     ],
                // ]);
                // $this->publishMessage($device->dev_id, $payload);


                //  thired version 
                //       $payload = $this->generateHexPayload(1, [
                //     [
                //         'type' => 'string',
                //         'value' => str_pad($user->id, 6, '0', STR_PAD_LEFT),
                //     ],
                //     [
                //         'type' => 'number',
                //         'value' => 0,
                //     ],
                //     [
                //         'type' => 'number16',
                //         'value' => $user->balance - $user->freezed_balance,
                //     ],
                // ]);
                // $this->publishMessage($device_id, $payload);
    }

    public function DeviceNotInSystem($device_id)
    {
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


    public function PhoneNumberNotFound($device_id)
    {
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
