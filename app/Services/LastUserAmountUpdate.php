<?php 


namespace App\Services;

 
use App\Models\LastUserAmount;
use App\Services\DeviceMessages;
use App\Providers\MQTTServiceProvider;

trait LastUserAmountUpdate {
     
 use DeviceMessages;

     public function UpdateOpModeOneLastAmount($user, $value2){
        $lastAmountCurrentDevice = LastUserAmount::where(
            'user_id',
            $user->id
        )
            ->where('device_id', $value2->id)
            ->first();

        if (empty($lastAmountCurrentDevice->user_id)) {
            LastUserAmount::insert([
                'user_id' => $user->id,
                'device_id' => $value2->id,
                'last_amount' =>
                $user->balance - $value2->tariff,
            ]);
        } else {
         
            $lastAmountCurrentDevice->last_amount =
                $user->balance - $value2->tariff_amount;    // aq iyo tariff amount manamde 
            $lastAmountCurrentDevice->save();
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
                $user->balance -$value2->tariff_amount    // aq iyo tariff amount manamde 
            ],
        ]);
        $this->publishMessage($value2->dev_id, $payload);
     }
}