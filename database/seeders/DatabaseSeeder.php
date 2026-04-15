<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate([
            'email' => 'admin@godiva.test',
        ], [
            'name' => 'Admin User',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        $categoryData = [
            'name' => 'Signature Truffles',
            'slug' => 'signature-truffles',
            'description' => 'Our finest assortment of signature chocolate truffles.',
            'is_active' => true,
            'updated_at' => now(),
        ];

        $category = DB::table('categories')->where('slug', 'signature-truffles')->first();
        if (! $category) {
            $catId = DB::table('categories')->insertGetId([
                ...$categoryData,
                'created_at' => now(),
            ]);
        } else {
            DB::table('categories')->where('id', $category->id)->update($categoryData);
            $catId = $category->id;
        }

        $products = [
            [
                'name' => 'Signature Gold Discovery Box',
                'price' => 65.00,
                'compare_at_price' => 80.00,
                'is_featured' => true,
                'is_new' => false,
                'image' => '/images/godiva/t1.png'
            ],
            [
                'name' => 'Dark Chocolate Truffle Flight',
                'price' => 45.00,
                'compare_at_price' => null,
                'is_featured' => true,
                'is_new' => true,
                'image' => '/images/godiva/t2.png'
            ],
            [
                'name' => 'Belgian Praline Assortment',
                'price' => 55.00,
                'compare_at_price' => 70.00,
                'is_featured' => true,
                'is_new' => false,
                'image' => '/images/godiva/t3.png'
            ],
            [
                'name' => 'Luxury Ganache Collection',
                'price' => 75.00,
                'compare_at_price' => null,
                'is_featured' => true,
                'is_new' => true,
                'image' => '/images/godiva/t4.png'
            ],
            [
                'name' => 'Masterpieces Oyster Box',
                'price' => 35.00,
                'compare_at_price' => 45.00,
                'is_featured' => false,
                'is_new' => true,
                'image' => '/images/godiva/t5.png'
            ],
            [
                'name' => 'Gold Ribbon Gift Set',
                'price' => 95.00,
                'compare_at_price' => 120.00,
                'is_featured' => false,
                'is_new' => false,
                'image' => '/images/godiva/t6.png'
            ],
            [
                'name' => 'Artisanal Dark Bar Trio',
                'price' => 25.00,
                'compare_at_price' => null,
                'is_featured' => false,
                'is_new' => true,
                'image' => '/images/godiva/t7.png'
            ],
            [
                'name' => 'Milk Chocolate Sea Shells',
                'price' => 30.00,
                'compare_at_price' => 40.00,
                'is_featured' => false,
                'is_new' => false,
                'image' => '/images/godiva/t1.png'
            ],
            [
                'name' => 'Signature Coffee & Truffles',
                'price' => 50.00,
                'compare_at_price' => null,
                'is_featured' => true,
                'is_new' => false,
                'image' => '/images/godiva/t2.png'
            ],
            [
                'name' => 'Grand Luxe Gift Pyramid',
                'price' => 150.00,
                'compare_at_price' => 180.00,
                'is_featured' => true,
                'is_new' => true,
                'image' => '/images/godiva/t3.png'
            ],
            [
                'name' => 'Cherry Cordials Tin',
                'price' => 28.00,
                'compare_at_price' => 35.00,
                'is_featured' => false,
                'is_new' => false,
                'image' => '/images/godiva/t4.png'
            ],
            [
                'name' => 'Ultimate Chocolate Tower',
                'price' => 225.00,
                'compare_at_price' => 275.00,
                'is_featured' => true,
                'is_new' => true,
                'image' => '/images/godiva/t5.png'
            ],
        ];

        foreach ($products as $p) {
            $productId = DB::table('products')->insertGetId([
                'category_id' => $catId,
                'name' => $p['name'],
                'slug' => Str::slug($p['name']),
                'description' => 'Experience the ultimate luxury with our ' . $p['name'] . '. Crafted with century-old Belgian traditions.',
                'price' => $p['price'],
                'compare_at_price' => $p['compare_at_price'],
                'sku' => 'GODV-' . Str::upper(Str::random(6)),
                'stock' => 100,
                'is_active' => true,
                'is_featured' => $p['is_featured'],
                'is_new' => $p['is_new'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_images')->insert([
                'product_id' => $productId,
                'image_path' => $p['image'],
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_variants')->insert([
                'product_id' => $productId,
                'name' => 'Size',
                'value' => 'Regular',
                'additional_price' => 0,
                'stock' => 50,
                'updated_at' => now(),
                'created_at' => now()
            ]);
        }
    }
}
