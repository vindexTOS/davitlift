<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

class DeviceUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch all user IDs and device IDs
        $userIds = User::all()->pluck('id')->toArray();
        $deviceIds = Device::all()->pluck('id')->toArray();

        // For the sake of this example, let's assume each user can be associated with multiple devices
        // and vice versa. We'll create random associations.

        foreach ($userIds as $userId) {
            // Randomly choose how many devices to associate with a user
            $numDevices = rand(1, 3);  // This is just an example. Adjust as needed.

            // Randomly select device IDs (without repeating)
            $chosenDeviceIds = array_rand(array_flip($deviceIds), $numDevices);

            foreach ((array)$chosenDeviceIds as $deviceId) {
                DB::table('device_user')->insert([
                    'user_id' => $userId,
                    'device_id' => $deviceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
