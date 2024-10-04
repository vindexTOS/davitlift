<?php



namespace App\Services;

use Carbon\Carbon;
use App\Models\Device;
use App\Models\DeviceEarn;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User;
use Symfony\Component\ErrorHandler\Debug;

trait UpdateDeviceEarnings
{




   public function UpdateDevicEarn(  $device)
   {
      // შესაძლოა დასჭირდეს დამატება კარტების გადახდისაც   $fixedCard = $userFixedBalnce * $userCardAmount ;
    
      $currentMonth = Carbon::now(4)->month;
      $currentYear = Carbon::now(4)->year;
      //  კომპანის ქეშბექის შემოწმება
      $companyUser = User::where('id', $device->users_id)->first();
      if ($companyUser->cashback == 0) {
         $companyUser  = User::where('id', $device->company_id)->first();
      }
  
       $device = Device::where('id', $device->id)->first();

      $deviceEarn = DeviceEarn::where('device_id', $device->id)
         ->where('month', $currentMonth)
         ->where('year', $currentYear)
         ->first();
        

         $deviceEarning = 0; 
 
      if (empty($deviceEarn)) {

         $deviceEarning = $device->tariff_amount;

        
         if (    $companyUser && $device) {
            if ($device->deviceTariffAmount !== null) {
               DeviceEarn::create([
                  'device_id' => $device->id,
                  'month' => $currentMonth,
                  'year' => $currentYear,
                  'earnings' => $deviceEarning,
                  'cashback' =>     $companyUser->cashback,
                  'deviceTariff' => $device->deviceTariffAmount,
               ]);
            } else {
               DeviceEarn::create([
                  'device_id' => $device->id,
                  'month' => $currentMonth,
                  'year' => $currentYear,
                  'earnings' => $deviceEarning,
                  'cashback' =>     $companyUser->cashback,
                  'deviceTariff' => 0,
               ]);
            }
         } else {

           
            DeviceEarn::create([
               'device_id' => $device->id,
               'month' => $currentMonth,
               'year' => $currentYear,
               'earnings' => $deviceEarning,
            ]);
         }
      } else {

         $deviceEarning =   $deviceEarn->earnings + $device->tariff_amount;

        
         if (    $companyUser && $device) {
            if ($device->deviceTariffAmount !== null) {
               $deviceEarn->earnings = $deviceEarning;
               $deviceEarn->cashback =     $companyUser->cashback;
               $deviceEarn->deviceTariff = $device->deviceTariffAmount;
               $deviceEarn->save();
            }
            $deviceEarn->earnings = $deviceEarning;
            $deviceEarn->cashback =     $companyUser->cashback;
            $deviceEarn->save();
         } else {
            $deviceEarn->earnings += $deviceEarning;
            $deviceEarn->save();
         }
      }
   }
}
