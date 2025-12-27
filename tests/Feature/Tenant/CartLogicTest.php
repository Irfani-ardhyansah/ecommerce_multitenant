<?php

namespace Tests\Feature\Tenant;

use Tests\TestCase;
use App\Models\Tenant\User;
use App\Models\Tenant\Product;
use App\Models\Tenant\Cart;
use Laravel\Sanctum\Sanctum;

class CartLogicTest extends TestCase
{
    public function test_cart_operations_update_product_stock_correctly()
    {
        $tenant = $this->createTenant('shop1');

        $tenant->run(function () use ($tenant) {
            // Setup Data
            $customer = User::create([
                'name' => 'Buyer', 
                'email' => 'buyer@shop.com', 
                'password' => bcrypt('pass'), 
                'role' => 'user'
            ]);

            $product = Product::create([
                'name' => 'Sepatu',
                'price' => 100000,
                'stock' => 10,
            ]);

            // Login Mock
            Sanctum::actingAs($customer, ['*'], 'tenant');

            // CASE A: Add to Cart
            // FIX: Gunakan Full URL Tenant
            $url = "http://" . $tenant->domains->first()->domain . "/api/cart";
            
            $res = $this->postJson($url, [
                'product_id' => $product->id,
                'quantity' => 2
            ]);

            $res->assertOk(); // Seharusnya sekarang 200 OK
            $this->assertEquals(8, $product->fresh()->stock);

            // CASE B: Fail Stock
            $resFail = $this->postJson($url, [
                'product_id' => $product->id,
                'quantity' => 9 
            ]);
            $resFail->assertStatus(422);

            // CASE C: Update
            $cartItem = Cart::first()->items->first();
            $urlUpdate = "http://" . $tenant->domains->first()->domain . "/api/cart/{$cartItem->id}";

            $resUpdate = $this->putJson($urlUpdate, [
                'quantity' => 5 
            ]);
            $resUpdate->assertOk();
            $this->assertEquals(5, $product->fresh()->stock);

            // CASE D: Delete
            $resDelete = $this->deleteJson($urlUpdate);
            $resDelete->assertOk();
            $this->assertEquals(10, $product->fresh()->stock);
        });
    }
}