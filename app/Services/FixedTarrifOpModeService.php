<?php



namespace   App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Device;
use App\Models\DeviceUser;
use Illuminate\Support\Facades\Log;
use App\Services\UpdateDeviceEarnings;



trait FixedTarrifOpModeService
{

    use UpdateDeviceEarnings;



    public function handleOpModeZeroSubscriptionCheck($user) {}


    public function handleOpModeZeroTransaction($user,  $device)
    {
        // მეორეჯერ ჩარიცხვის შემთხვევაში არ უნდა გაუქტიურდეს თავიდან, თუ უკვე აქტიური აქვს 



        if ($user->balance >= $device->tariff_amount && $device->tariff_amount > 0) {

            // ავიღოთ უსერის საბსქრიბშიენი
            $deviceUser = DeviceUser::where('device_id', $device->id)
                ->where('user_id', $user->id)
                ->first();

            // გამოვთვალოთ შემდეგი თვე ჩარიცხვის დღიდან
            $nextMonthPayDay = Carbon::now()->addMonth()->startOfDay();

            // დღვეანდელი დღე 
            $today = Carbon::now()->startOfDay();


            //  ვამოწმებთ თუ უსერს უკვე აქვს გააქტიურებული თუ არა 
            if (Carbon::parse($deviceUser->subscription)->lte($today)) {
                Log::debug("gamarjoba robika");

                //  ვაჭრით თანხას უსერს 
                $user->balance =  $user->balance - $device->tariff_amount;
                DeviceUser::where('device_id', $device->id)
                    ->where('user_id', $user->id)
                    ->update(['subscription' => $nextMonthPayDay]);
                $user->save();
                //  ვააბთეიდთებთ ერნინგებს

                $this->UpdateDevicEarn($device);
            }
        }
    }



 public function ReturnSubscriptionTypeToDevice( $userDevice , $data, $device){
    Log::debug("MQTT CONTROLLER Carbon");
    $payload = $this->generateHexPayload(4, [
        [
            'type' => 'timestamp',
            'value' => Carbon::parse($userDevice->subscription)
                ->timestamp,
        ],
        [
            'type' => 'string',
            'value' => $data['payload'],
        ],
        [
            'type' => 'number',
            'value' => 0,
        ],
    ]);
    $this->publishMessage($device->dev_id, $payload);

 }

}
