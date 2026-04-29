<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder {
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void {
        // Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        Role::firstOrCreate(['name' => 'Staff']);
        Role::firstOrCreate(['name' => 'Customer']);

        $masterAdmin = User::query()->updateOrCreate([
            'email' => 'master@admin.com',
        ], [
            'name'              => 'masteradmin',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);
        $masterAdmin->assignRole($superAdminRole);

        $administrator = User::query()->updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name'              => 'administrator',
            'password'          => bcrypt('111111'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);
        $administrator->assignRole($adminRole);

        $categoryRows = [
            [
                'name' => 'Signature Truffles',
                'slug' => 'signature-truffles',
                'description' => 'Velvety ganache and classic Belgian truffle favorites.',
                'image' => '/images/godiva/truffles.png',
            ],
            [
                'name' => 'Gift Boxes',
                'slug' => 'gift-boxes',
                'description' => 'Elegant chocolate assortments for thoughtful gifting.',
                'image' => '/images/godiva/gift_boxes.png',
            ],
            [
                'name' => 'Seasonal Joy',
                'slug' => 'seasonal-joy',
                'description' => 'Limited-edition creations for celebrations and sweet moments.',
                'image' => '/images/godiva/seasonal.png',
            ],
        ];

        $categoryIds = [];
        foreach ($categoryRows as $categoryData) {
            $category = DB::table('categories')->where('slug', $categoryData['slug'])->first();
            $payload = [
                ...$categoryData,
                'is_active' => true,
                'updated_at' => now(),
            ];

            if (! $category) {
                $categoryIds[$categoryData['slug']] = DB::table('categories')->insertGetId([
                     ...$payload,
                    'created_at' => now(),
                ]);
            } else {
                DB::table('categories')->where('id', $category->id)->update($payload);
                $categoryIds[$categoryData['slug']] = $category->id;
            }
        }

        $products = [
            [
                'name'             => 'Signature Gold Discovery Box',
                'price'            => 65.00,
                'compare_at_price' => 80.00,
                'is_featured'      => true,
                'is_new'           => false,
                'image'            => '/images/godiva/t1.png',
                'category_slug'    => 'gift-boxes',
            ],
            [
                'name'             => 'Dark Chocolate Truffle Flight',
                'price'            => 45.00,
                'compare_at_price' => null,
                'is_featured'      => true,
                'is_new'           => true,
                'image'            => '/images/godiva/t2.png',
                'category_slug'    => 'signature-truffles',
            ],
            [
                'name'             => 'Belgian Praline Assortment',
                'price'            => 55.00,
                'compare_at_price' => 70.00,
                'is_featured'      => true,
                'is_new'           => false,
                'image'            => '/images/godiva/t3.png',
                'category_slug'    => 'gift-boxes',
            ],
            [
                'name'             => 'Luxury Ganache Collection',
                'price'            => 75.00,
                'compare_at_price' => null,
                'is_featured'      => true,
                'is_new'           => true,
                'image'            => '/images/godiva/t4.png',
                'category_slug'    => 'signature-truffles',
            ],
            [
                'name'             => 'Masterpieces Oyster Box',
                'price'            => 35.00,
                'compare_at_price' => 45.00,
                'is_featured'      => false,
                'is_new'           => true,
                'image'            => '/images/godiva/t5.png',
                'category_slug'    => 'seasonal-joy',
            ],
            [
                'name'             => 'Gold Ribbon Gift Set',
                'price'            => 95.00,
                'compare_at_price' => 120.00,
                'is_featured'      => false,
                'is_new'           => false,
                'image'            => '/images/godiva/t6.png',
                'category_slug'    => 'gift-boxes',
            ],
            [
                'name'             => 'Artisanal Dark Bar Trio',
                'price'            => 25.00,
                'compare_at_price' => null,
                'is_featured'      => false,
                'is_new'           => true,
                'image'            => '/images/godiva/t7.png',
                'category_slug'    => 'seasonal-joy',
            ],
            [
                'name'             => 'Milk Chocolate Sea Shells',
                'price'            => 30.00,
                'compare_at_price' => 40.00,
                'is_featured'      => false,
                'is_new'           => false,
                'image'            => '/images/godiva/t1.png',
                'category_slug'    => 'signature-truffles',
            ],
            [
                'name'             => 'Signature Coffee & Truffles',
                'price'            => 50.00,
                'compare_at_price' => null,
                'is_featured'      => true,
                'is_new'           => false,
                'image'            => '/images/godiva/t2.png',
                'category_slug'    => 'signature-truffles',
            ],
            [
                'name'             => 'Grand Luxe Gift Pyramid',
                'price'            => 150.00,
                'compare_at_price' => 180.00,
                'is_featured'      => true,
                'is_new'           => true,
                'image'            => '/images/godiva/t3.png',
                'category_slug'    => 'gift-boxes',
            ],
            [
                'name'             => 'Cherry Cordials Tin',
                'price'            => 28.00,
                'compare_at_price' => 35.00,
                'is_featured'      => false,
                'is_new'           => false,
                'image'            => '/images/godiva/t4.png',
                'category_slug'    => 'seasonal-joy',
            ],
            [
                'name'             => 'Ultimate Chocolate Tower',
                'price'            => 225.00,
                'compare_at_price' => 275.00,
                'is_featured'      => true,
                'is_new'           => true,
                'image'            => '/images/godiva/t5.png',
                'category_slug'    => 'gift-boxes',
            ],
        ];

        foreach ($products as $p) {
            $slug = Str::slug($p['name']);
            $existingProduct = DB::table('products')->where('slug', $slug)->first();
            $categoryId = $categoryIds[$p['category_slug']] ?? $categoryIds['signature-truffles'];

            if (! $existingProduct) {
                $productId = DB::table('products')->insertGetId([
                    'category_id'      => $categoryId,
                    'name'             => $p['name'],
                    'slug'             => $slug,
                    'description'      => 'Experience the ultimate luxury with our ' . $p['name'] . '. Crafted with century-old Belgian traditions.',
                    'price'            => $p['price'],
                    'compare_at_price' => $p['compare_at_price'],
                    'sku'              => 'GODV-' . Str::upper(Str::random(6)),
                    'stock'            => 100,
                    'is_active'        => true,
                    'is_featured'      => $p['is_featured'],
                    'is_new'           => $p['is_new'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'image_path' => $p['image'],
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('product_variants')->insert([
                    'product_id'       => $productId,
                    'name'             => 'Size',
                    'value'            => 'Regular',
                    'additional_price' => 0,
                    'stock'            => 50,
                    'updated_at'       => now(),
                    'created_at'       => now(),
                ]);
            } else {
                DB::table('products')->where('id', $existingProduct->id)->update([
                    'category_id' => $categoryId,
                    'price' => $p['price'],
                    'compare_at_price' => $p['compare_at_price'],
                    'is_featured' => $p['is_featured'],
                    'is_new' => $p['is_new'],
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

                DB::table('product_images')->updateOrInsert(
                    ['product_id' => $existingProduct->id, 'sort_order' => 0],
                    [
                        'image_path' => $p['image'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $sliders = [
            [
                'title'       => 'Legacy made in <br/> <span class="font-normal italic">chocolate</span>',
                'subtitle'    => 'New Centennial Collection',
                'description' => 'Since 1926, our passion for chocolate has been an endless pursuit of savours and sensations. Our Centennial Pralines are both sweetly nostalgic and at the cutting edge of chocolate design and innovation.',
                'image'       => '/images/godiva/hero_stack.png',
                'bg_color'    => '#1C1C1C', // Charcoal
                'text_color'  => '#FFFFFF',
                'button_text' => 'Discover the Collection',
                'button_link' => '#',
                'sort_order'  => 1,
            ],
            [
                'title'       => 'Art of <br/> Belgian Gifting',
                'subtitle'    => 'Exquisite Gifting',
                'description' => 'Delight your senses with our premium golden gift collections, crafted with the finest ingredients and century-old traditions.',
                'image'       => '/images/godiva/hero2.png',
                'bg_color'    => '#FBE0E3', // Godiva Pink
                'text_color'  => '#1C1C1C',
                'button_text' => 'Shop Gift Boxes',
                'button_link' => '#',
                'sort_order'  => 2,
            ],
            [
                'title'       => 'The <br/> <span class="font-normal italic">Seasonal</span> Collection',
                'subtitle'    => 'Limited Edition',
                'description' => 'Explore our limited-time creations, where seasonal flavors meet artisanal craftsmanship.',
                'image'       => '/images/godiva/seasonal.png',
                'bg_color'    => '#F3EFE9', // Cream
                'text_color'  => '#1C1C1C',
                'button_text' => 'View Seasonal Items',
                'button_link' => '#',
                'sort_order'  => 3,
            ],
        ];

        foreach ($sliders as $s) {
            DB::table('sliders')->updateOrInsert(
                ['title' => $s['title']],
                [
                     ...$s,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        DB::table('web_settings')->updateOrInsert(
            ['id' => 1],
            [
                'site_name' => 'SweetChocholate',
                'logo' => '/images/godiva/logo-cute.png',
                'footer_logo' => '/images/godiva/logo-cute.png',
                'favicon' => '/images/godiva/logo-cute.png',
                'email' => 'hello@sweetchocolate.test',
                'phone' => '+880 1700 000000',
                'address' => 'Dhaka, Bangladesh',
                'maintenance_mode' => false,
                'maintenance_title' => 'We are polishing the shop',
                'maintenance_message' => 'SweetChocholate is temporarily unavailable while we make a few improvements.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $footerPages = [
            'about-us' => [
                'title' => 'About Us',
                'content' => '<h2>Our Chocolate Story</h2><p>SweetChocholate creates premium chocolate gifts, truffles, bars, and seasonal collections with careful sourcing and thoughtful presentation.</p><p>Update this page from Admin > Static Pages to share your brand story, mission, and values.</p>',
            ],
            'employment' => [
                'title' => 'Employment',
                'content' => '<h2>Careers At SweetChocholate</h2><p>Share current openings, hiring policies, team culture, and application instructions here.</p>',
            ],
            'retail-store-locations' => [
                'title' => 'Retail Store Locations',
                'content' => '<h2>Visit Our Stores</h2><p>Add your store addresses, opening hours, phone numbers, and map details from the admin panel.</p>',
            ],
            'factory-tours' => [
                'title' => 'Factory Tours',
                'content' => '<h2>Factory Tours</h2><p>Use this page to describe tour schedules, booking rules, visitor guidelines, and group packages.</p>',
            ],
            'terms-of-service' => [
                'title' => 'Terms of Service',
                'content' => '<h2>Terms of Service</h2><p>Add your store terms, order rules, user responsibilities, and legal conditions here.</p>',
            ],
            'contact-us' => [
                'title' => 'Contact Us',
                'content' => '<h2>Contact SweetChocholate</h2><p>Email: hello@sweetchocolate.test</p><p>Phone: +880 1700 000000</p><p>Address: Dhaka, Bangladesh</p>',
            ],
            'wholesale' => [
                'title' => 'Wholesale',
                'content' => '<h2>Wholesale Orders</h2><p>Describe bulk pricing, corporate gifting, reseller requirements, and contact instructions for wholesale customers.</p>',
            ],
            'faq' => [
                'title' => 'FAQ',
                'content' => '<h2>Frequently Asked Questions</h2><h3>How can I order?</h3><p>Customers can shop directly from the website and complete checkout online.</p><h3>Can I customize gifts?</h3><p>Add your customization details and policies here.</p>',
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'content' => '<h2>Privacy Policy</h2><p>Explain how customer data is collected, used, stored, and protected.</p>',
            ],
            'shipping-policy' => [
                'title' => 'Shipping Policy',
                'content' => '<h2>Shipping Policy</h2><p>Add delivery areas, shipping timelines, charges, and temperature-sensitive packaging notes here.</p>',
            ],
            'refund-policy' => [
                'title' => 'Refund Policy',
                'content' => '<h2>Refund Policy</h2><p>Describe refund eligibility, damaged item claims, replacement rules, and support contact details.</p>',
            ],
            'factory-expansion' => [
                'title' => 'Factory Expansion',
                'content' => '<h2>Factory Expansion</h2><p>Share updates about production growth, new facilities, community impact, and announcements here.</p>',
            ],
        ];

        foreach ($footerPages as $slug => $page) {
            if (! DB::table('pages')->where('slug', $slug)->exists()) {
                DB::table('pages')->insert([
                    'slug' => $slug,
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $menus = [
            [
                'name' => 'Shop Chocolates',
                'url' => '/shop',
                'order' => 1,
                'children' => [
                    ['name' => 'All Chocolates', 'url' => '/shop', 'order' => 1],
                    ['name' => 'Signature Truffles', 'url' => '/categories/signature-truffles', 'order' => 2],
                    ['name' => 'Gift Boxes', 'url' => '/categories/gift-boxes', 'order' => 3],
                    ['name' => 'Seasonal Joy', 'url' => '/categories/seasonal-joy', 'order' => 4],
                ],
            ],
            [
                'name' => 'Craft Chocolate Bars',
                'url' => '/shop?q=bar',
                'order' => 2,
                'children' => [
                    ['name' => 'Dark Chocolate', 'url' => '/shop?q=dark', 'order' => 1],
                    ['name' => 'Milk Chocolate', 'url' => '/shop?q=milk', 'order' => 2],
                    ['name' => 'Artisanal Trio', 'url' => '/shop?q=bar', 'order' => 3],
                ],
            ],
            [
                'name' => 'Gift Ideas',
                'url' => '/categories/gift-boxes',
                'order' => 3,
                'children' => [
                    ['name' => 'Gift Boxes', 'url' => '/categories/gift-boxes', 'order' => 1],
                    ['name' => 'Luxury Gifts', 'url' => '/shop?q=luxury', 'order' => 2],
                    ['name' => 'Sale Gifts', 'url' => '/shop?sale=1', 'order' => 3],
                ],
            ],
            [
                'name' => 'Shop By Flavor',
                'url' => '/shop',
                'order' => 4,
                'children' => [
                    ['name' => 'Truffle', 'url' => '/shop?q=truffle', 'order' => 1],
                    ['name' => 'Praline', 'url' => '/shop?q=praline', 'order' => 2],
                    ['name' => 'Ganache', 'url' => '/shop?q=ganache', 'order' => 3],
                    ['name' => 'Cherry', 'url' => '/shop?q=cherry', 'order' => 4],
                ],
            ],
        ];

        DB::table('menus')
            ->whereNull('parent_id')
            ->whereNotIn('name', array_column($menus, 'name'))
            ->update(['is_active' => false, 'updated_at' => now()]);

        foreach ($menus as $menu) {
            $children = $menu['children'];
            unset($menu['children']);

            DB::table('menus')->updateOrInsert(
                ['name' => $menu['name']],
                [
                     ...$menu,
                    'parent_id' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $parentId = DB::table('menus')->where('name', $menu['name'])->value('id');

            DB::table('menus')
                ->where('parent_id', $parentId)
                ->whereNotIn('name', array_column($children, 'name'))
                ->update(['is_active' => false, 'updated_at' => now()]);

            foreach ($children as $child) {
                DB::table('menus')->updateOrInsert(
                    ['name' => $child['name'], 'parent_id' => $parentId],
                    [
                         ...$child,
                        'parent_id' => $parentId,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
