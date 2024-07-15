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
    
    public function TestFixedCard (){
        
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
                $userFixedBalance = $user->fixed_card_amount;
                $userCardAmount = Card::where('user_id', $user->id)->count();
                $fixedCard = $userFixedBalance * $userCardAmount;
                
                $subscriptionDate = $user->pivot->subscription
                ? Carbon::parse($user->pivot->subscription)
                : null;
                
                $nextMonthPayDay = Carbon::now(4)
                ->addMonth()
                ->startOfMonth()
                ->addDays($device->pay_day - 1);
                
                if ($device->tariff_amount == 0 || $device->tariff_amount <= 0 || $device->tariff_amount == "0") {
                    $userBalance = $user->balance;
                    
                    // Handle user balances and subscriptions
                    if ($user->balance >= $fixedCard &&
                    $user->freezed_balance >= $fixedCard &&
                    !is_null($subscriptionDate) &&
                    $subscriptionDate->lt($nextMonthPayDay)) {
                        
                        DB::beginTransaction();
                        
                        try {
                            $user->balance -= $fixedCard;
                            $user->freezed_balance -= $fixedCard;
                            
                            // Update subscription date for next month
                            $currentDay = Carbon::now(4)->day;
                            $nextMonthPayDay = ($currentDay < $device->pay_day)
                            ? Carbon::now(4)->startOfMonth()->addDays($device->pay_day - 1)
                            : Carbon::now(4)->addMonth()->startOfMonth()->addDays($device->pay_day - 1);
                            
                            DeviceUser::where('device_id', $device->id)
                            ->where('user_id', $user->id)
                            ->update(['subscription' => $nextMonthPayDay]);
                            
                            $user->save();
                            $deviceEarning += $fixedCard;
                            
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollback();
                            $this->error('Failed to process user ' . $user->id . ' with device ' . $device->id . ': ' . $e->getMessage());
                        }
                    }
                } else {
                    // Handle cases where device tariff is greater than zero
                    if ($user->balance >= $device->tariff_amount + $fixedCard &&
                    $user->freezed_balance >= $device->tariff_amount &&
                    !is_null($subscriptionDate) &&
                    $subscriptionDate->lt($nextMonthPayDay)) {
                        
                        DB::beginTransaction();
                        
                        try {
                            $user->balance -= $device->tariff_amount + $fixedCard;
                            $user->freezed_balance -= $device->tariff_amount;
                            
                            $currentDay = Carbon::now(4)->day;
                            $nextMonthPayDay = ($currentDay < $device->pay_day)
                            ? Carbon::now(4)->startOfMonth()->addDays($device->pay_day - 1)
                            : Carbon::now(4)->addMonth()->startOfMonth()->addDays($device->pay_day - 1);
                            
                            DeviceUser::where('device_id', $device->id)
                            ->where('user_id', $user->id)
                            ->update(['subscription' => $nextMonthPayDay]);
                            
                            $user->save();
                            $deviceEarning += $device->tariff_amount + $fixedCard;
                            
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollback();
                            $this->error('Failed to process user ' . $user->id . ' with device ' . $device->id . ': ' . $e->getMessage());
                        }
                    }
                }
            }
            
            // Update Device earnings
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
                // Create a new DeviceEarn record
                DeviceEarn::create([
                    'device_id' => $device->id,
                    'month' => $currentMonth,
                    'year' => $currentYear,
                    'earnings' => $deviceEarning,
                    'cashback' => $user->cashback,
                    'deviceTariff' => $device->deviceTariffAmount ?? 0,
                ]);
            } else {
                // Update existing DeviceEarn record
                if ($user && $device) {
                    $deviceEarn->earnings += $deviceEarning;
                    $deviceEarn->cashback = $user->cashback;
                    $deviceEarn->deviceTariff = $device->deviceTariffAmount ?? 0;
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
}
