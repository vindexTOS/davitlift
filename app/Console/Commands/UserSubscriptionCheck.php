<?php

namespace App\Console\Commands;

use App\Models\DeviceUser;
use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\User;
use App\Models\DeviceEarn;

// Assume this is the name of your device earning model
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserSubscriptionCheck extends Command
{
    protected $signature = 'user:subscription-check';
    protected $description = 'Check user subscriptions and update balances and earnings accordingly';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
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
            $users = $device->users; // Assuming DeviceUser is the related model name, and 'users' is the relationship method name in Device model.

            foreach ($users as $user) {
                $subscriptionDate = $user->pivot->subscription ? Carbon::parse($user->pivot->subscription) : null;
                $nextMonthPayDay = Carbon::now()->addMonth()->startOfMonth()->addDays($device->pay_day - 1);
                if ($user->balance >= $device->tariff_amount
                    && $user->freezed_balance >= $device->tariff_amount
                    && !is_null($subscriptionDate)
                    && $subscriptionDate->lt($nextMonthPayDay)
                ) {
                    // Start transaction
                    DB::beginTransaction();
                    try {

                        // Update User balances
                        $user->balance -= $device->tariff_amount;
                        $user->freezed_balance -= $device->tariff_amount;

                        if (($user->balance - $user->freezed_balance) >= $device->tariff_amount) {

                            DeviceUser::where('device_id', $device->id)->where('user_id', $user->id)->update(
                                ['subscription' => $nextMonthPayDay]
                            );

                            $user->freezed_balance = $user->freezed_balance + $device->tariff_amount;
                        }

                        $user->save();


                        // Update device earnings
                        $deviceEarning += $device->tariff_amount;
                        // Commit the transaction
                        DB::commit();
                    } catch (\Exception $e) {
                        // An error occurred; rollback the transaction
                        DB::rollback();
                        $this->error('Failed to process user ' . $user->id . ' with device ' . $device->id . ': ' . $e->getMessage());
                    }
                }
            }
            $deviceEarn = DeviceEarn::where('device_id', $device->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();
            if (empty($deviceEarn)) {
                DeviceEarn::create([
                    'device_id' => $device->id,
                    'month' => $currentMonth,
                    'year' => $currentYear,
                    'earnings' => $deviceEarning,
                ]);
            } else {
                $deviceEarn->earnings += $deviceEarning;
                $deviceEarn->save();
            }
        }

        $this->info('User subscriptions have been checked and updated successfully.');
    }
}
