<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $categories = [
            2 => 'phone',
            3 => 'tv',
            4 => 'laptop',
            6 => 'T-shirt',
            7 => 'pants',
            8 => 'shoes'
        ];

        foreach (range(1, 2502) as $index) {
            $categoryId = array_rand($categories); // Select a random category ID
            $categoryName = $categories[$categoryId];

            // Generate a realistic product name
            $name = match ($categoryName) {
                'phone' => $faker->company . ' ' . $faker->randomElement(['X', 'Pro', 'Max', 'Ultra', 'Mini']),
                'tv' => $faker->company . ' ' . $faker->randomElement(['4K OLED', 'Smart TV', 'Ultra HD', 'QLED', 'HDR']),
                'laptop' => $faker->company . ' ' . $faker->randomElement(['Notebook', 'Gaming Laptop', 'Ultrabook', 'ProBook']),
                'T-shirt' => $faker->colorName . ' ' . $faker->randomElement(['Cotton Tee', 'Sport Tee', 'V-neck', 'Polo Shirt']),
                'pants' => $faker->colorName . ' ' . $faker->randomElement(['Jeans', 'Joggers', 'Chinos', 'Cargo Pants']),
                'shoes' => $faker->company . ' ' . $faker->randomElement(['Running Shoes', 'Sneakers', 'Boots', 'Sandals']),
                default => 'Unknown Product'
            };

            // Generate a more detailed, natural product description
            $about = $faker->sentence(15) . ' It is perfect for ' . $faker->randomElement([
                'daily use.',
                'professional needs.',
                'sports enthusiasts.',
                'casual wear.',
                'tech lovers.'
            ]);

            Product::create([
                'name' => $name,
                'about' => $about,
                'category_id' => $categoryId, // Store the category ID
                'prix' => $faker->randomFloat(2, 10, 2000),
                'stock' => $faker->randomFloat(2, 10, 2000),
                'is_valid' => 1,
                'seller_id' => 1
            ]);
        }
    }
}
