<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use App\Models\DeviceEarn;

// Assume this is the name of your device earning model
use App\Models\DeviceUser;
use Illuminate\Console\Command;
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
                $userFixedBalnce = $user->fixed_card_amount;
                $userCardAmount = Card::where('user_id', $user->id)->count();
                $fixedCard = $userFixedBalnce * $userCardAmount;
                $subscriptionDate = $user->pivot->subscription
                    ? Carbon::parse($user->pivot->subscription)
                    : null;
                $nextMonthPayDay = Carbon::now()
                    ->addMonth()
                    ->startOfMonth()
                    ->addDays($device->pay_day - 1);
                // როცა დევაისის ტარიფი უდრის ნულს
                if ($device->tariff_amunt == 0) {
                    $userBalance = $user->balance;
                    $fixedCard;

                    if (
                        $userBalance >= $fixedCard &&
                        !is_null($subscriptionDate) &&
                        $subscriptionDate->lt($nextMonthPayDay)
                    ) {
                        DB::beginTransaction();

                        try {
                            $user->balance -= $fixedCard;
                            $currentDay = Carbon::now()->day;

                            if ($currentDay < $device->pay_day) {
                                $nextMonthPayDay = Carbon::now()
                                    ->startOfMonth()
                                    ->addDays($device->pay_day - 1);
                            } else {
                                $nextMonthPayDay = Carbon::now()
                                    ->addMonth()
                                    ->startOfMonth()
                                    ->addDays($device->pay_day - 1);
                            }
                            DeviceUser::where('device_id', $device->id)
                                ->where('user_id', $user->id)
                                ->update([
                                    'subscription' => $nextMonthPayDay,
                                ]);

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
                    if (
                        $user->balance >= $device->tariff_amount + $fixedCard &&
                        $user->freezed_balance >= $device->tariff_amount &&
                        !is_null($subscriptionDate) &&
                        $subscriptionDate->lt($nextMonthPayDay)
                    ) {
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
                                $currentDay = Carbon::now()->day;

                                if ($currentDay < $device->pay_day) {
                                    $nextMonthPayDay = Carbon::now()
                                        ->startOfMonth()
                                        ->addDays($device->pay_day - 1);
                                } else {
                                    $nextMonthPayDay = Carbon::now()
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

        $this->info(
            'User subscriptions have been checked and updated successfully.'
        );
    }
}
