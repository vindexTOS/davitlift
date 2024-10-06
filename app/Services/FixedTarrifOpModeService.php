<?php



namespace   App\Services;

use Carbon\Carbon;

use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use App\Models\DeviceUser;
use Illuminate\Support\Facades\Log;
use App\Services\UpdateDeviceEarnings;



trait FixedTarrifOpModeService
{

    use UpdateDeviceEarnings;



    public function handleOpModeZeroSubscriptionCheck($deviceUser,   $device)
    {




        Log::info("user", ["user" => $deviceUser->subscription]);
        if (is_null($deviceUser->subscription)) {
            // Try another old date
            $deviceUser->subscription = '2000-01-01 00:00:00';
            $deviceUser->save();
        }

        if (Carbon::parse($deviceUser->subscription) < Carbon::now()->startOfDay()) {

            $user = User::where("id", $deviceUser->user_id)->first();
            $combinedTarffToBepayed =  $this->GetCardTotalAmount($user, $device->tariff_amount);


            if ($user->balance >= $combinedTarffToBepayed &&  $combinedTarffToBepayed > 0) {

                $user->balance = $user->balance -    $combinedTarffToBepayed;

                $nextMonthPayDay = Carbon::now()->addMonth()->startOfDay();

                DeviceUser::where('device_id', $device->id)
                    ->where('user_id', $deviceUser->user_id)
                    ->update(['subscription' => $nextMonthPayDay]);


                $user->save();


                $this->UpdateDevicEarn($device, $combinedTarffToBepayed);
            }
        }
    }


    public function handleOpModeZeroTransaction($user,  $device)
    {
        // მეორეჯერ ჩარიცხვის შემთხვევაში არ უნდა გაუქტიურდეს თავიდან, თუ უკვე აქტიური აქვს 



        $combinedTarffToBepayed =  $this->GetCardTotalAmount($user, $device->tariff_amount);


        if ($user->balance >=  $combinedTarffToBepayed &&  $combinedTarffToBepayed > 0) {

            // ავიღოთ უსერის საბსქრიბშიენი
            $deviceUser = DeviceUser::where('device_id', $device->id)
                ->where('user_id', $user->id)
                ->first();

            // გამოვთვალოთ შემდეგი თვე ჩარიცხვის დღიდან
            $nextMonthPayDay = Carbon::now()->addMonth()->startOfDay();

            // დღვეანდელი დღე 
            $today = Carbon::now()->startOfDay();

            if (is_null($deviceUser->subscription)) {
                // Try another old date
                $deviceUser->subscription = '2000-01-01 00:00:00';
                $deviceUser->save();
            }

            //  ვამოწმებთ თუ უსერს უკვე აქვს გააქტიურებული თუ არა 
            if (Carbon::parse($deviceUser->subscription)->lte($today)) {

                //  ვაჭრით თანხას უსერს 
                $user->balance =  $user->balance -  $combinedTarffToBepayed;
                DeviceUser::where('device_id', $device->id)
                    ->where('user_id', $user->id)
                    ->update(['subscription' => $nextMonthPayDay]);
                $user->save();
                //  ვააბთეიდთებთ ერნინგებს

                $this->UpdateDevicEarn($device,  $combinedTarffToBepayed);
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
}
