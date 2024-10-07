<?php

namespace   App\Services;

use App\Models\LastUserAmount;
use App\Providers\MQTTServiceProvider;
use App\Services\DeviceMessages;
use Illuminate\Support\Facades\Log;
use App\Services\UpdateDeviceEarnings;






trait TarriffCardOpModeService
{
    use DeviceMessages;
    use UpdateDeviceEarnings;

    //    უცვლელად დავტოვე რადგან პრობლემა არ ქონია ამ კოდს 

    public function handleOpModeOneTransaction($user, $device, $OP_MODE, $dataPayload  = null)
    {
        if ((int) $user->balance - $device->tariff_amount >= $device->tariff_amount) {

            $user->balance =    $user->balance -  $device->tariff_amount ;
            $user->save();

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
                    $user->balance -  $device->tariff_amount;


                $lastAmount->save();

                Log::info("else ", ["op" => $OP_MODE]);
            }

   Log::info("info" , ["info"=>$dataPayload]);


            $payload = $this->generateHexPayload(3, [
                [
                    'type' => 'string',
                    'value' => str_pad($user->id, 6, '0', STR_PAD_LEFT),
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                ['type' => 'string',
                 'value' => $dataPayload],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'number16',
                    'value' => $user->balance - $device->tariff_amount ,
                ],
            ]);

            $this->UpdateDevicEarn($device, $device->tariff_amount);

         
            $this->publishMessage($device->dev_id, $payload);
        } else {
            $this->noMoney($device->dev_id);
        }
    }
}
