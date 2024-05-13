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
{    public function userSubscriptionCheck()
    {
        $today = Carbon::now()->day;
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Get Devices where pay_day is equal to today and op_mode is equal to 0
        $devices = Device::where('pay_day', $today)
        ->where('op_mode', 0)
        ->get();
        
        
        
        foreach ($devices as $device) {
            $deviceEarning = 0;
            $users = $device->users;
            
            foreach ($users as $user) {
                $subscriptionDate = $user->pivot->subscription
                ? Carbon::parse($user->pivot->subscription)
                : null;
                $nextMonthPayDay = Carbon::now()
                ->addMonth()
                ->startOfMonth()
                ->addDays($device->pay_day - 1);
                
                $cardCount = Card::where("user_id", $user->id)->count();
                // card counter 
                
                if (
                    $user->balance >= $device->tariff_amount  &&
                    $user->freezed_balance >= $device->tariff_amount &&
                    !is_null($subscriptionDate) &&
                    $subscriptionDate->lt($nextMonthPayDay)
                    ) {
                        Log::debug( "sub active" );
                        // Start transaction
                        DB::beginTransaction();
                        try {
                            // Update User balances
                            $user->balance -= $device->tariff_amount;
                            $user->freezed_balance -= $device->tariff_amount;
                            
                            if (
                                $user->balance - $user->freezed_balance >=
                                $device->tariff_amount
                                ) {
                                    DeviceUser::where('device_id', $device->id)
                                    ->where('user_id', $user->id)
                                    ->update(['subscription' => $nextMonthPayDay]);
                                    
                                    $user->freezed_balance +=
                                    $device->tariff_amount;
                                }
                                
                                $user->save();
                                
                                // Update device earnings
                                $deviceEarning += $device->tariff_amount;
                                // Commit the transaction
                                DB::commit();
                            } catch (\Exception $e) {
                                // An error occurred; rollback the transaction
                                DB::rollback();
                                Log::error(
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
                            DeviceEarn::create([
                                'device_id' => $device->id,
                                'month' => $currentMonth,
                                'year' => $currentYear,
                                'earnings' => $deviceEarning,
                                'cashback' => $user->cashback,
                                'deviceTariff' => $device->deviceTariffAmount,
                            ]);
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
                            $deviceEarn->earnings += $deviceEarning;
                            $deviceEarn->cashback = $user->cashback;
                            $deviceEarn->deviceTariff = $device->deviceTariffAmount;
                            $deviceEarn->save();
                        } else {
                            $deviceEarn->earnings += $deviceEarning;
                            $deviceEarn->save();
                        }
                    }
                }
                
                
                return 'User subscriptions have been checked and updated successfully.';
            }
            
        }
        