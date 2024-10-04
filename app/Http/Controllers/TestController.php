<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DeviceUser;
use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\User;
use App\Models\DeviceEarn;
use App\Models\Card;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SubscriptionService;

class TestController extends   Controller 
{  
 





 
    public function TestTimeZone()
    {
        $currentTime = Carbon::now('Asia/Tbilisi');
        $currentTimeFormatted = $currentTime->format('Y-m-d H:i:s');
        
        return response()->json([
            'currentTime' =>  $currentTimeFormatted,
            'app-time-zone' => config('app.timezone'),
            'todat'=>Carbon::now('Asia/Tbilisi') ,
        ]);
    }
    

    public function FixedCardOpMode2(){




    }
    
  

    public function TestFixedCard () { 
        $today = Carbon::now(4)->day;
       $currentMonth = Carbon::now(4)->month;
       $currentYear = Carbon::now(4)->year;

       // Get Devices where pay_day is equal to today and op_mode is equal to 0
       $devices = Device::where('pay_day', $today)
           ->where('op_mode', 0)
           ->get();
        
            
       foreach ($devices as $device) {

           $deviceEarning = 0;
           $users = $device->users; // Assuming DeviceUser is the related model name, and 'users' is the relationship method name in Device model.
           foreach ($users as $user) {
            //   უსერის საბსქრიბშენის აფდეითი 
           }
       // დარჩეს როგორც არის 
           $user = User::where('id', $device->users_id)->first();
           if ($user->cashback == 0) {
               $user = User::where('id', $device->company_id)->first();
           }
           $device = Device::where('id', $device->id)->first();
           $deviceEarn = DeviceEarn::where('device_id', $device->id)
               ->where('month', $currentMonth)
               ->where('year', $currentYear)
               ->first();
        
           if (empty($deviceEarn)) {
               if ($user && $device) {
                   if( $device->deviceTariffAmount !== null           ){
                       DeviceEarn::create([
                           'device_id' => $device->id,
                           'month' => $currentMonth,
                           'year' => $currentYear,
                           'earnings' => $deviceEarning,
                           'cashback' => $user->cashback,
                           'deviceTariff' => $device->deviceTariffAmount,
                       ]);
                   }else{
                       DeviceEarn::create([
                           'device_id' => $device->id,
                           'month' => $currentMonth,
                           'year' => $currentYear,
                           'earnings' => $deviceEarning,
                           'cashback' => $user->cashback,
                           'deviceTariff' => 0,
                       ]);
                   }
               
               } else {
                   DeviceEarn::create([
                       'device_id' => $device->id,
                       'month' => $currentMonth,
                       'year' => $currentYear,
                       'earnings' => $deviceEarning,
                   ]);
               }
           } else {
               if ($user && $device) {
                   if ($device->deviceTariffAmount !== null) {
                       $deviceEarn->earnings += $deviceEarning;
                       $deviceEarn->cashback = $user->cashback;
                       $deviceEarn->deviceTariff = $device->deviceTariffAmount;
                       $deviceEarn->save();
                   }
                   $deviceEarn->earnings += $deviceEarning;
                   $deviceEarn->cashback = $user->cashback;
                   $deviceEarn->save();
               } else {
                   $deviceEarn->earnings += $deviceEarning;
                   $deviceEarn->save();
               }
           }
       }


       return response()->json(
        ['message' =>  Carbon::now(4)->startOfMonth()->addDays(15 - 1)],
        200
    );
   }
// 

     
  
 
    //   
       
    
}
