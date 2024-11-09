<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use App\Models\DeviceEarn;
use App\Models\DeviceUser;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use App\Services\DeviceMessages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SubscriptionService;
use App\Services\FixedTarrifOpModeService;

class TestController extends   Controller
{

    use FixedTarrifOpModeService;

    use DeviceMessages;



    public function TestTimeZone()
    {
        $currentTime = Carbon::now('Asia/Tbilisi');
        $currentTimeFormatted = $currentTime->format('Y-m-d H:i:s');

        return response()->json([
            'currentTime' =>  $currentTimeFormatted,
            'app-time-zone' => config('app.timezone'),
            'todat' => Carbon::now('Asia/Tbilisi'),
        ]);
    }




    public function FixBalanceForNew($device_id)
    {

        $deviceUsers = DeviceUser::where("device_id", $device_id)->get();
        $device = Device::where("id", $device_id)->first();
        $today = Carbon::now(4)->day;
        $currentMonth = Carbon::now(4)->month;
        $currentYear = Carbon::now(4)->year;
        if ($device->op_mode == '0') {


            foreach ($deviceUsers as $deviceUser) {

                $user = User::where('id', $deviceUser->user_id)->first();       
                         $fullAmount = $this->GetCardTotalAmount($user, $device->tariff_amount);

                if ($user->balance >=   $fullAmount &&   $fullAmount > 0) {

                    $user->balance = $user->balance - $fullAmount;
                    $user->save();
                        if($device){
                            $deviceEarn = DeviceEarn::where('device_id', $device->id)
                            ->where('month', $currentMonth)
                            ->where('year', $currentYear)
                            ->first();
    
                            if(empty($deviceEarn)){
                            if ($device->deviceTariffAmount !== null) {
 
               DeviceEarn::create([
                  'device_id' => $device->id,
                  'month' => $currentMonth,
                  'year' => $currentYear,
                  'earnings' => $fullAmount,
                  'cashback' =>     0,
                  'deviceTariff' => $device->deviceTariffAmount,
               ]);
            } else {
               DeviceEarn::create([
                  'device_id' => $device->id,
                  'month' => $currentMonth,
                  'year' => $currentYear,
                  'earnings' => $fullAmount,
                  'cashback' =>     0,
                  'deviceTariff' => 0,
               ]);
            }
                            }
                            
                            else{
                                $deviceEarn->earnings +=  $fullAmount;
    
                                $deviceEarn->save();
                            }
    
                
                        }
             
                }
            }
        }
    }
    private function GetCardTotalAmount($user, $deviceAmount)
    {

        $deviceCardNumber = Card::where("user_id", $user->id)->count();

        $deviceTarrifAmountCombined = $deviceCardNumber * $user->fixed_card_amount;

        $combinedTarffToBepayed = $deviceAmount + $deviceTarrifAmountCombined;

        return $combinedTarffToBepayed;
    }
    public function TestFixedCard($device_id)
    {

        Log::info("user", ['user' => "TEST"]);
        // Get Devices where pay_day is equal to today and op_mode is equal to 0
        $devices = Device::where('id', $device_id)
            ->get();


        foreach ($devices as $device) {

            $deviceUsers = DeviceUser::where('device_id', $device->id)->get();

            foreach ($deviceUsers as $user) {
                $this->handleOpModeZeroSubscriptionCheck($user, $device);
            }
        }


        return response()->json(
            ['message' =>  Carbon::now(4)->startOfMonth()->addDays(15 - 1)],
            200
        );
    }
    // 

   public function sendTestMessageToDevice($device_id, $message){
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
            'value' => $message,
        ],
        [
            'type' => 'number',
            'value' => 0,
        ],
    ]);

    $this->publishMessage($device_id, $payload);

   }


    //   


}
