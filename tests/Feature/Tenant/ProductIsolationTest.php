<?php

namespace Tests\Feature\Tenant;

use Tests\TestCase;
use App\Models\Tenant\User;
use App\Models\Tenant\Product;
use Laravel\Sanctum\Sanctum;

class ProductIsolationTest extends TestCase
{
    public function test_product_crud_and_data_isolation_between_tenants()
    {
        $tenant1 = $this->createTenant('tenant1');
        $tenant2 = $this->createTenant('tenant2');

        // --- Tenant 1 ---
        $tenant1->run(function () use ($tenant1) {
            $admin1 = User::create(['name' => 'A1', 'email' => 'a@t1.com', 'password' => bcrypt('p'), 'role' => 'admin']);
            Sanctum::actingAs($admin1, ['*'], 'tenant');

            // FIX URL
            $url = "http://" . $tenant1->domains->first()->domain . "/api/products";

            $this->postJson($url, [
                'name' => 'Produk T1',
                'price' => 100,
                'stock' => 10
            ])->assertCreated(); // Assert 201
        });

        // --- Tenant 2 ---
        $tenant2->run(function () use ($tenant2) {
            $this->assertDatabaseMissing('products', ['name' => 'Produk T1']);

            $admin2 = User::create(['name' => 'A2', 'email' => 'a@t2.com', 'password' => bcrypt('p'), 'role' => 'admin']);
            Sanctum::actingAs($admin2, ['*'], 'tenant');

            // FIX URL
            $url = "http://" . $tenant2->domains->first()->domain . "/api/products";

            $this->postJson($url, [
                'name' => 'Produk T2',
                'price' => 50,
                'stock' => 5
            ])->assertCreated();
        });
    }
}