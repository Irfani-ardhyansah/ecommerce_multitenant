<?php

namespace Tests\Feature\Central;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Laravel\Sanctum\Sanctum;

class TenantManagementTest extends TestCase
{
    public function test_super_admin_can_create_tenant_and_database_is_created()
    {
        // 1. Setup Super Admin
        $admin = User::create([
            'name' => 'Super Admin', 
            'email' => 'admin@central.com', 
            'password' => bcrypt('password')
        ]);

        // Login sebagai Admin Central
        Sanctum::actingAs($admin, ['*'], 'web');

        // 2. Action: Hit API Create Tenant
        $payload = [
            'id' => 'tokabudi',
            'domain' => 'tokobudi',
            'email' => 'owner@tokobudi.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/central/tenants', $payload);

        // 3. Assert Response
        $response->assertStatus(201);
        
        // 4. Assert Data di Central DB
        $this->assertDatabaseHas('tenants', ['id' => 'tokabudi']);
        $this->assertDatabaseHas('domains', ['domain' => 'tokobudi.localhost']); // Sesuaikan config url_base

        // 5. Assert Database Fisik Terbuat (Integration Test)
        // Kita cek apakah bisa connect ke database tenant baru itu
        $tenant = Tenant::find('tokabudi');
        $this->assertNotNull($tenant);

        // Coba masuk ke context tenant dan cek apakah user admin toko terbuat
        $tenant->run(function () {
            $this->assertDatabaseHas('users', ['email' => 'owner@tokobudi.com']);
        });
    }
}