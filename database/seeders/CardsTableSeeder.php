<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class CardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cards')->insert([
            'card_number' => Str::random(6),  // Assuming a random 16-char string as a card number
            'name' => 'John Doe',
            'user_id' => 1,  // Assuming a hardcoded user id for simplicity
            'device_id' => 1,  // Assuming a hardcoded device id for simplicity
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
