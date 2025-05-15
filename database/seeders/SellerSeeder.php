<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class SellerSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $storeNames = ['Samsung', 'Condor', 'LG', 'Apple', 'Xiaomi', 'Huawei', 'Dell', 'Asus', 'HP', 'Lenovo'];

        $sellers = DB::table('users')->where('role_id', 2)->get();
        $usedPhones = [];

        foreach ($sellers as $index => $user) {
            // Ensure unique phone number
            do {
                $phone = '0' . $faker->numberBetween(500000000, 599999999);
            } while (in_array($phone, $usedPhones));
            $usedPhones[] = $phone;

            DB::table('sellers')->insert([
                'id' => $index + 1,
                'user_id' => $user->id,
                'store' => $storeNames[array_rand($storeNames)] . ' Store',
                'phone' => $phone,
                'adress' => $faker->address,
                'status' => 'accepted',
                'paypal' => strtoupper(Str::random(10)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
