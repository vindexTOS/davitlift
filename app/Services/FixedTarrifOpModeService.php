<?php



namespace   App\Services;

use App\Models\Device;
use Carbon\Carbon;
use App\Models\User;
use App\Models\DeviceUser;
use Illuminate\Support\Facades\Log;



trait FixedTarrifOpModeService
{




    public function handleOpModeZeroSubscriptionCheck($user) {


     


    }


    public function handleOpModeZeroTransaction(  $user,  $device)
    {     
        // მეორეჯერ ჩარიცხვის შემთხვევაში არ უნდა გაუქტიურდეს თავიდან, თუ უკვე აქტიური აქვს 
     
      

if ($user->balance   >= $device->tariff_amount && $device->tariff_amount > 0) {

    // ავიღოთ უსერის საბსქრიბშიენი
    $deviceUser = DeviceUser::where('device_id', $device->id)
        ->where('user_id', $user->id)
        ->first();

    // გამოვთვალოთ შემდეგი თვე ჩარიცხვის დღიდან
    $nextMonthPayDay = Carbon::now()->addMonth()->startOfDay();

    // დღვეანდელი დღე 
    $today = Carbon::now()->startOfDay();
    Log::debug("gamarjoba robika 1");

    //  ვამოწმებთ თუ უსერს უკვე აქვს გააქტიურებული თუ არა 
    if  (  Carbon::parse($deviceUser->subscription)->lte($today))  {
        Log::debug("gamarjoba robika");

        //  ვაჭრით თანხას უსერს 
            $user->balance =  $user->balance - $device->tariff_amount;
         DeviceUser::where('device_id', $device->id)
            ->where('user_id', $user->id)
            ->update(['subscription' => $nextMonthPayDay]);
             $user->save();   
    }
}
 }
}
