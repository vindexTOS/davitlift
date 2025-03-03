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




   public function UpdateDevicEarn($device,  $combinedTarffToBepayed)
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



      $managerId = $device['users_id'];
      $companyId = $device["company_id"];
      if (empty($deviceEarn)) {





         if ($companyUser && $device) {
            Log::info("shemosvla 3", ['info' => $combinedTarffToBepayed]);

            if ($device->deviceTariffAmount !== null) {
               Log::info("shemosvla 4", ['info' => $combinedTarffToBepayed]);

               DeviceEarn::create([
                  'device_id' => $device->id,
                  'month' => $currentMonth,
                  'year' => $currentYear,
                  'earnings' => $combinedTarffToBepayed,
                  'cashback' =>     $companyUser->cashback,
                  'deviceTariff' => $device->deviceTariffAmount,
                  "manager_id" =>  $managerId,
                  "company_id" => $companyId
               ]);
            } else {
               DeviceEarn::create([
                  'device_id' => $device->id,
                  'month' => $currentMonth,
                  'year' => $currentYear,
                  'earnings' => $combinedTarffToBepayed,
                  'cashback' =>     $companyUser->cashback,
                  'deviceTariff' => 0,
                  "manager_id" =>  $managerId,
                  "company_id" => $companyId
               ]);
            }
         } else {


            DeviceEarn::create([
               'device_id' => $device->id,
               'month' => $currentMonth,
               'year' => $currentYear,
               'earnings' => $combinedTarffToBepayed,
               "manager_id" =>  $managerId,
               "company_id" => $companyId
            ]);
         }
      } else {




         if ($companyUser && $device) {
            Log::info("shemosvla 5", ['info' => $combinedTarffToBepayed]);

            if ($device->deviceTariffAmount !== null) {
               $deviceEarn->deviceTariff = $device->deviceTariffAmount;
            }
            $deviceEarn->earnings = $deviceEarn->earnings  + $combinedTarffToBepayed;
            $deviceEarn->cashback = $companyUser->cashback;
            $deviceEarn->save();
         } else {
            Log::info("shemosvla 6", ['info' => $combinedTarffToBepayed]);

            $deviceEarn->earnings = $deviceEarn->earnings  + $combinedTarffToBepayed;
            $deviceEarn->save();
         }
      }
   }
}
