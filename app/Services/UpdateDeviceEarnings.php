<?php 



namespace App\Services;

use Carbon\Carbon;
use App\Models\Device;
use App\Models\DeviceEarn;
use Illuminate\Foundation\Auth\User;




trait UpdateDeviceEarnings {
    



     public function UpdateDevicEarn($user ,$device){
 // შესაძლოა დასჭირდეს დამატება კარტების გადახდისაც   $fixedCard = $userFixedBalnce * $userCardAmount ;
 $today = Carbon::now(4)->day;
 $currentMonth = Carbon::now(4)->month;
 $currentYear = Carbon::now(4)->year;    
//  კომპანის ქეშბექის შემოწმება
 $companyUser = User::where('id', $device->users_id)->first();
 if ($companyUser ->cashback == 0) {
    $companyUser  = User::where('id', $device->company_id)->first();
 }

 $device = Device::where('id', $device->id)->first();

 $deviceEarn = DeviceEarn::where('device_id', $device->id)
 ->where('month', $currentMonth)
 ->where('year', $currentYear)
 ->first();
  
        $user->balance;
        $deviceEarning  += $device->tariff_amount;
     }
}