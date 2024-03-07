<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed 10 users for example
        for ($i = 0; $i < 10; $i++) {
            User::create([
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Example password: 'password'
                'phone' => '12345679'.$i, // An example phone number, adjust accordingly
                'balance' => rand(0, 10000) / 100, // Random balance between 0 and 100
            ]);
        }
    }
}
