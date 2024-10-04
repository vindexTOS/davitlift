<?php



namespace   App\Services;

use App\Models\Device;
use Carbon\Carbon;
use App\Models\User;
use App\Models\DeviceUser;
use Illuminate\Support\Facades\Log;



trait FixedTarrifOpModeService
{




    public function handleOpModeZeroSubscriptionCheck($user) {}


    public function handleOpModeZeroTransaction(  $user,  $device)
    {     
        // მეორეჯერ ჩარიცხვის შემთხვევაში არ უნდა გაუქტიურდეს თავიდან, თუ უკვე აქტიური აქვს 
        Log::info("robika", ["shemovla robikashi"=>$user ]);

if ($user->balance - $user->freezed_balance >= $device->tariff_amount && $device->tariff_amount > 0) {

    // ავიღოთ უსერის საბსქრიბშიენი
    $deviceUser = DeviceUser::where('device_id', $device->id)
        ->where('user_id', $user->id)
        ->first();

    // გამოვთვალოთ შემდეგი თვე ჩარიცხვის დღიდან
    $nextMonthPayDay = Carbon::now()->addMonth()->startOfDay();

    // დღვეანდელი დღე 
    $today = Carbon::now()->startOfDay();

    //  ვამოწმებთ თუ უსერს უკვე აქვს გააქტიურებული თუ არა 
    if  (!$deviceUser || !$deviceUser->subscription || Carbon::parse($deviceUser->subscription)->lte($today))  {

         DeviceUser::where('device_id', $device->id)
            ->where('user_id', $user->id)
            ->update(['subscription' => $nextMonthPayDay]);
             $user->save();   
    }
}




            // $subscriptionDate = $device->pivot->subscription
            //     ? Carbon::parse($device->pivot->subscription)
            //     : null;
            // $currentDay = Carbon::now()->day;
            // if ($currentDay < $device->pay_day) {
            //     $nextMonthPayDay = Carbon::now()
            //         ->startOfMonth()
            //         ->addDays($device->pay_day - 1);
            // } else {
            //     $nextMonthPayDay = Carbon::now()
            //         ->addMonth()
            //         ->startOfMonth()
            //         ->addDays($device->pay_day - 1);
            // }
            // //   ამ იფის შემოწმება არ არის საჭირო უსერ საბსქრიშენის კერნელის შემოწმებაში რადგან უსერის დამატების დროს უკვე ისედაც უნულდებათ საბსქრიბშენ თარიღი
            // if (
            //     is_null($subscriptionDate) ||
            //     ($subscriptionDate &&
            //         $subscriptionDate->lt($nextMonthPayDay) &&  $userCardAmount > 0 &&  $user->balance >= $device->tariff_amount && $device->tariff_amount > 0 ||  $device->fixed_card_amount > 0  && $device->fixed_card_amount  <=  $user->balance)
            // ) {
            //     if (
            //         $userCardAmount > 0 &&  $user->balance - $user->freezed_balance >=
            //         $device->tariff_amount &&    $device->tariff_amount > 0
            //     ) {
            //         DeviceUser::where('device_id', $device->id)
            //             ->where('user_id', $user->id)
            //             ->update(['subscription' => $nextMonthPayDay]);
            //         $user->freezed_balance =
            //             $user->freezed_balance + $device->tariff_amount;
            //     }
            // }
        
    }
}
