<?php

namespace   App\Services;

 use App\Models\LastUserAmount;
 use App\Providers\MQTTServiceProvider;
use App\Services\DeviceMessages;
use App\Services\UpdateDeviceEarnings;






trait TarriffCardOpModeService
{
    use DeviceMessages;
    use UpdateDeviceEarnings;

//    უცვლელად დავტოვე რადგან პრობლემა არ ქონია ამ კოდს 

    public function handleOpModeOneTransaction($user, $device)
    {
        $lastAmount = LastUserAmount::where(
            'user_id',
            $user->id
        )
            ->where('device_id',  $device->id)
            ->first();

        if (empty($lastAmount->user_id)) {
            LastUserAmount::insert([
                'user_id' => $user->id,
                'device_id' => $device->id,
                'last_amount' =>
                $user->balance - $device->tariff_amount,
            ]);
        } else {
            $lastAmount->last_amount =
                $user->balance -  $device->tariff_amount ;
            $lastAmount->save();
        }
        $payload = $this->generateHexPayload(5, [
            [
                'type' => 'string',
                'value' => str_pad(
                    $user->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'number16',
                'value' =>
                $user->balance - $device->tariff_amount,
            ],
        ]);
        $this->UpdateDevicEarn($device);
        $this->publishMessage($device->dev_id, $payload);
    }
}
