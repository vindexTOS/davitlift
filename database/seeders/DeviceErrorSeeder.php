<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DeviceErrorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deviceIds = DB::table('devices')->pluck('id')->toArray();

        // Now let's create some mock device errors using Laravel's Eloquent ORM
        foreach ($deviceIds as $deviceId) {
            // Generate random errors for each device
            $errorCount = rand(1, 5); // Generate between 1 and 5 errors for each device as an example

            for ($i = 0; $i < $errorCount; $i++) {
                DB::table('device_errors')->insert([
                    'device_id' => $deviceId,
                    'errorCode' =>  rand(100, 999), // Random error codes for the example
                    'errorText' => 'This is a mock error text for device ' . $deviceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
