<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant\User;     
use App\Models\Tenant\Cart;     
use App\Models\Tenant\CartItem;     
use App\Models\Tenant\Product; 

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@tenant1.com'],
            [
                'name' => 'Admin Toko',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'user@tenant1.com'],
            [
                'name' => 'Customer Setia',
                'password' => Hash::make('password'),
                'role' => 'user', 
            ]
        );

        $products = [
            [
                'name' => 'Laptop Gaming ROG',
                'description' => 'Laptop spesifikasi tinggi untuk gaming dan coding.',
                'price' => 25000000,
                'stock' => 5,
                'image' => 'https://via.placeholder.com/150',
            ],
            [
                'name' => 'Mouse Wireless Logitech',
                'description' => 'Mouse hening tanpa suara klik.',
                'price' => 150000,
                'stock' => 50,
                'image' => 'https://via.placeholder.com/150',
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'Keyboard enak buat ngetik seharian.',
                'price' => 750000,
                'stock' => 20,
                'image' => 'https://via.placeholder.com/150',
            ],
        ];

        foreach ($products as $item) {
            $product = Product::firstOrCreate(
                ['name' => $item['name']], 
                $item
            );
            $createdProducts[] = $product;
        }

        $this->command->info('Data Tenant (User & Produk) berhasil di-seed!');

        $cart = Cart::firstOrCreate(['user_id' => $customer->id]);

        // Ambil produk dari array yang kita buat diatas
        $laptop = $createdProducts[0]; // Laptop ROG
        $mouse = $createdProducts[1];  // Mouse Logitech

        // Masukkan Laptop ke Cart
        CartItem::firstOrCreate([
            'cart_id' => $cart->id,
            'product_id' => $laptop->id,
        ], [
            'quantity' => 1
        ]);

        // Masukkan Mouse ke Cart
        CartItem::firstOrCreate([
            'cart_id' => $cart->id,
            'product_id' => $mouse->id,
        ], [
            'quantity' => 2
        ]);

        $this->command->info('Shopping Cart populated for Customer.');
    }
}