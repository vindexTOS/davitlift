<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company; // Assuming the model for 'companies' table is 'Company'

class CompaniesTableSeeder extends Seeder
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

        // Seed 10 companies for example
        for ($i = 0; $i < 10; $i++) {
            Company::create([
                'company_name' => $faker->company,
                'admin_id' => $i < 4 ? 1 : 2, // Assuming you want a UUID, adjust if needed
                'sk_code' => $faker->swiftBicNumber, // Assuming you want a swift/bic number for sk_code, adjust if needed
                'comment' => $faker->sentence,
            ]);
        }
    }
}
