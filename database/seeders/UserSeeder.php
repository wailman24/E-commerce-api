<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    public function run()
    {
        $arabicNames = ['Ahmed', 'Akram', 'Omar', 'Youssef', 'Rami', 'Nour', 'Hassan', 'Samir', 'Hadi', 'Tarek'];
        $otherNames = ['John', 'Emily', 'Liam', 'Sophia', 'Noah', 'Emma', 'Oliver', 'Mia', 'James', 'Ava'];
        $names = array_merge($arabicNames, $otherNames);

        $usedEmails = [];
        $csvData = "id,password\n";

        for ($i = 51; $i <= 400; $i++) {
            $name = $names[array_rand($names)];

            // Ensure unique email
            do {
                $randomNumber = rand(100, 999);
                $email = strtolower($name) . $randomNumber . '@gmail.com';
            } while (in_array($email, $usedEmails));

            $usedEmails[] = $email;


            $plainPassword = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $hashedPassword = bcrypt($plainPassword);

            DB::table('users')->insert([
                'id' => $i,
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'role_id' => 3,
                'password' => $hashedPassword,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $csvData .= "$i,$plainPassword\n";
        }

        // Save CSV of unhashed passwords
        Storage::disk('local')->put('clients_passwords.csv', $csvData);
        if (Storage::disk('local')->exists('clients_passwords.csv')) {
            echo " clients_passwords CSV saved successfully .\n";
        } else {
            echo "clients_passwords Failed to save CSV  .\n";
        }
    }
}
