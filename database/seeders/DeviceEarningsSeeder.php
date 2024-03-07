<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Device;  // Import the Device model

class DeviceEarningsSeeder extends Seeder
{
    public function run()
    {
        // Retrieve all devices
        $devices = Device::all();

        foreach ($devices as $device) {
            // Here, I'm seeding earnings data for each of the past 12 months for each device
            for ($i = 0; $i < 12; $i++) {
                $date = Carbon::now()->subMonths($i);
                $monthYear = $date->month . $date->year;

                // Seed data for this device and monthYear
                DB::table('device_earn')->insert([
                    'device_id' => $device->id,
                    'earnings' => mt_rand(50, 200) / 10,  // Sample random earning between 5.0 to 20.0
                    'month_year' => $monthYear,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
