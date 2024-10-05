<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DeviceUser;
use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\User;
use App\Models\DeviceEarn;
use App\Models\Card;
use App\Services\FixedTarrifOpModeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SubscriptionService;

class TestController extends   Controller
{

    use FixedTarrifOpModeService;





    public function TestTimeZone()
    {
        $currentTime = Carbon::now('Asia/Tbilisi');
        $currentTimeFormatted = $currentTime->format('Y-m-d H:i:s');

        return response()->json([
            'currentTime' =>  $currentTimeFormatted,
            'app-time-zone' => config('app.timezone'),
            'todat' => Carbon::now('Asia/Tbilisi'),
        ]);
    }


    public function FixedCardOpMode2() {}



    public function TestFixedCard($device_id)
    {
    

        // Get Devices where pay_day is equal to today and op_mode is equal to 0
        $devices = Device::where('id', $device_id)
            ->get();


        foreach ($devices as $device) {

            $deviceUsers = DeviceUser::where('device_id', $device->id)->get();

            foreach ($deviceUsers as $user) {
                $this->handleOpModeZeroSubscriptionCheck($user, $device);
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
