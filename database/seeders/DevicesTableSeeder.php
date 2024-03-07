<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device; // Assuming the model for 'devices' table is 'Device'

class DevicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Using Faker to generate random data
        $faker = \Faker\Factory::create();

        // Seed 10 devices for demonstration
        for ($i = 0; $i < 10; $i++) {
            Device::create([
                'startup' => $faker->randomNumber(3),
                'relay_pulse_time' => $faker->randomDigit,
                'lcd_brightness' => $faker->randomDigit,
                'led_brightness' => $faker->randomDigit,
                'msg_appear_time' => $faker->randomDigit,
                'card_read_delay' => $faker->randomDigit,
                'tariff_amount' => $faker->randomDigit,
                'timezone' => $faker->randomDigit,
                'storage_disable' => $faker->boolean,
                'relay1_node' => $faker->boolean,
                'dev_name' => 'dev'.$i,
                'op_mode' => $faker->word,
                'dev_id' => 'dev_id'.$i,
                'guest_msg_L1' => $faker->text(16),
                'guest_msg_L2' => $faker->text(16),
                'guest_msg_L3' => $faker->text(16),
                'validity_msg_L1' => $faker->text(16),
                'validity_msg_L2' => $faker->text(16),
                'name' => $faker->name,
                'comment' => $faker->sentence,
                'company_id' => $i < 5 ? 1 : 2, // You may want to adjust this if you want to attach companies
                'users_id' => $i < 5 ? 1 : 2, // Adjust if you want to attach users
                'soft_version' => $faker->word,
                'hardware_version' => $faker->word,
                'url' => $faker->url,
                'crc32' => substr($faker->md5, 0, 8)  // Randomly generating a CRC32-like string
            ]);
        }
    }
}
