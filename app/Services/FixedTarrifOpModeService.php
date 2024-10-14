<?php



namespace   App\Services;

use Carbon\Carbon;

use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use App\Models\DeviceUser;
use App\Providers\NotificationProvider;
use Illuminate\Support\Facades\Log;
use App\Services\UpdateDeviceEarnings;



trait FixedTarrifOpModeService
{

    use UpdateDeviceEarnings;
    use DeviceMessages;
    use NotificationProvider;

    public function handleOpModeZeroSubscriptionCheck($deviceUser,   $device)
    {




        if (is_null($deviceUser->subscription)) {

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

    // დატა პეილოადი მოდის დევაისიდან, ამ ფუნქციას ყოველთვის არ ჭირდება ეს პარამეტრი ამიტომაც დეფაულტად არის ნული
    public function handleOpModeZeroTransaction($user,  $device, $dataPayload =  null)
    {
        // მეორეჯერ ჩარიცხვის შემთხვევაში არ უნდა გაუქტიურდეს თავიდან, თუ უკვე აქტიური აქვს 

   Log::info("shemosvla", ['handle functiashi'=>$user]);

        $combinedTarffToBepayed =  $this->GetCardTotalAmount($user, $device->tariff_amount);


        if ($user->balance >=  $combinedTarffToBepayed &&  $combinedTarffToBepayed > 0) {



            // ავიღოთ უსერის საბსქრიბშიენი
            $deviceUser = DeviceUser::where('user_id', $user->id)
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

            //  ვამოწმებთ თუ უსერს უკვე აქვს გააქტიურებული თუ არა 12
            if (Carbon::parse($deviceUser->subscription)->lte($today)) {

                //  ვაჭრით თანხას უსერს 
                $user->balance =  $user->balance -  $combinedTarffToBepayed;
                //  es im shemtxvevashi tu devices gawerili aqvs rom yovel tve erti da imave dros iyos gadaxda
                if ($device->isFixed == '0') {
                    DeviceUser::where('user_id', $user->id)
                        ->update(['subscription' => $nextMonthPayDay]);
                } else {

                    $nextPayDay = $this->isFixedMonthCalculator($device);
                    DeviceUser::where('user_id', $user->id)
                        ->update(['subscription' => $nextPayDay->toDateString()]);
                }



                $user->save();


                //  ვააბთეიდთებთ ერნინგებს

                $this->UpdateDevicEarn($device,  $combinedTarffToBepayed);
                if (!is_null($dataPayload)) {
                    if ($device->isFixed == '0') {
                        $user->subscription = Carbon::now()->addMonth()->startOfDay();
                        $notificationDateTime  =  Carbon::parse($user->subscription);
                    } else {
                        $user->subscription = $nextPayDay = $this->isFixedMonthCalculator($device);
                        Carbon::parse($user->subscription);
                    }
                    $this->ReturnSubscriptionTypeToDevice($user, $dataPayload, $device);
                }

                $notificationTarrifTobePayed = $combinedTarffToBepayed  / 100;
             
                $this->createUserGenericNotification($user->id, "თქვენ ჩამოგეჭრათ $notificationTarrifTobePayed ლარი და გაგიაქტიურდათ ულიმიტო ტარიფი   $notificationDateTime -მდე", "+", \App\Enums\NotificationType::transaction);
            }
        } else {
            $this->noMoney($device->dev_id);
        }
    }
    private function GetCardTotalAmount($user, $deviceAmount)
    {

        $deviceCardNumber = Card::where("user_id", $user->id)->count();

        $deviceTarrifAmountCombined = $deviceCardNumber * $user->fixed_card_amount;

        $combinedTarffToBepayed = $deviceAmount + $deviceTarrifAmountCombined;

        return $combinedTarffToBepayed;
    }

    private function isFixedMonthCalculator($device)
    {
        $payDay = $device->pay_day;
        $today = Carbon::now();
        // vamowmebt to shemdegi tarigi am tveshia tu shemdeg tveshi
        $nextPayDay = Carbon::createFromDate($today->year, $today->month, $payDay);
        if ($today->greaterThan($nextPayDay)) {

            return    $nextPayDay->addMonth();
        }

        return $nextPayDay;
    }
}
