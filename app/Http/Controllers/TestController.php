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
               $userFixedBalnce = $user->fixed_card_amount;
               $userCardAmount = Card::where('user_id', $user->id)->count();
               $fixedCard = $userFixedBalnce * $userCardAmount ;
               


               $subscriptionDate = $user->pivot->subscription
                   ? Carbon::parse($user->pivot->subscription)
                   : null;
               $nextMonthPayDay = Carbon::now(4)
                   ->addMonth()
                   ->startOfMonth()
                   ->addDays($device->pay_day - 1);
               // როცა დევაისის ტარიფი უდრის ნულს


               if ($device->tariff_amount == 0 || $device->tariff_amount <= 0 || $device->tariff_amount == "0") {
                   $userBalance = $user->balance;
                  
                   $user->freezed_balance = $fixedCard;
                                       if (
                           $user->balance >=$fixedCard &&
                           $user->freezed_balance >= $fixedCard &&
                       !is_null($subscriptionDate) &&
                       $subscriptionDate->lt($nextMonthPayDay)
                   ) {
                       DB::beginTransaction();

                       try {
                           $user->balance -= $fixedCard ;
                           
                           $user->freezed_balance -= $fixedCard;
                           if( $user->balance - $user->freezed_balance >= $fixedCard){
                           $currentDay = Carbon::now()->day;

                           if ($currentDay < $device->pay_day) {
                               $nextMonthPayDay = Carbon::now(4)
                                   ->startOfMonth()
                                   ->addDays($device->pay_day - 1);
                           } else {
                               $nextMonthPayDay = Carbon::now(4)
                                   ->addMonth()
                                   ->startOfMonth()
                                   ->addDays($device->pay_day - 1);
                           }
                           DeviceUser::where('device_id', $device->id)
                               ->where('user_id', $user->id)
                               ->update([
                                   'subscription' => $nextMonthPayDay,
                               ]);
   }
                           $user->save();
                           $deviceEarning += $fixedCard;
                           DB::commit();
                       } catch (\Exception $e) {
                           DB::rollback();
                           $this->error(
                               'Failed to process user ' .
                                   $user->id .
                                   ' with device ' .
                                   $device->id .
                                   ': ' .
                                   $e->getMessage()
                           );
                       }
                   }

                   //  როცა დვაისის ტარიფი ნოლზე მეტია
               } else {

                Log::debug("If is garet" , ["user id"=> $user->balance , $user->freezed_balance ]);

                   if (
                       $user->balance >= $device->tariff_amount + $fixedCard &&
                       $user->freezed_balance >= $device->tariff_amount &&
                       !is_null($subscriptionDate) &&
                       $subscriptionDate->lt($nextMonthPayDay)
                   ) { 

                    Log::debug("shemosvla");
                       // Start transaction
                       DB::beginTransaction();
                       try {
                           // Update User balances

                           $user->balance -=
                               $device->tariff_amount + $fixedCard;

                           $user->freezed_balance -= $device->tariff_amount;

                           $deviceTariffWithCardBalance =
                               $device->tariff_amount + $fixedCard;
                           
                           if (
                               $user->balance - $user->freezed_balance >=
                               $deviceTariffWithCardBalance
                           ) {

                            Log::debug("shemosvla device trafikshi" , ["user id"=> $user->id]);

                               $currentDay = Carbon::now()->day;

                               if ($currentDay < $device->pay_day) {
                                   $nextMonthPayDay = Carbon::now(4)
                                       ->startOfMonth()
                                       ->addDays($device->pay_day - 1);
                               } else {
                                   $nextMonthPayDay = Carbon::now(4)
                                       ->addMonth()
                                       ->startOfMonth()
                                       ->addDays($device->pay_day - 1);
                               }
                               DeviceUser::where('device_id', $device->id)
                                   ->where('user_id', $user->id)
                                   ->update([
                                       'subscription' => $nextMonthPayDay,
                                   ]);

                               $user->freezed_balance =
                                   $user->freezed_balance +
                                   $device->tariff_amount;
                           }

                           $user->save();

                           // Update device earnings
                           $deviceEarning += $deviceTariffWithCardBalance;
                           // Commit the transaction
                           DB::commit();
                       } catch (\Exception $e) {
                           // An error occurred; rollback the transaction
                           DB::rollback();
                           $this->error(
                               'Failed to process user ' .
                                   $user->id .
                                   ' with device ' .
                                   $device->id .
                                   ': ' .
                                   $e->getMessage()
                           );
                       }
                   }
               }
           }

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
