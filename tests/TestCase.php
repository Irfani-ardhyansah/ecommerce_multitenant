<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    // Hapus trait RefreshDatabase di sini karena kita handle manual untuk multi-db
    
    protected function setUp(): void
    {
        parent::setUp();
        // dd([
        //     'Current Environment' => app()->environment(), // Wajib: 'testing'
        //     'Database Central'    => config('database.connections.mysql.database'), // Wajib: 'central_test'
        //     'Tenant DB Prefix'    => config('tenancy.database.prefix'), // Wajib: 'test_tenant_' (sesuai .env.testing)
        //     'App URL'             => config('app.url'),
        // ]);
        // 1. Migrasi Database Central setiap kali test jalan
        $this->artisan('migrate:fresh', [
            '--database' => 'mysql', // Koneksi central
            '--path' => 'database/migrations' // Path migrasi central
        ]);
    }

    protected function tearDown(): void
    {
        // 2. Bersihkan semua Tenant & Database-nya setelah test selesai
        // Ini mencegah error "Database exists" saat test dijalankan ulang
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $tenant->delete(); // Ini akan men-trigger Job DeleteDatabase
        }

        parent::tearDown();
    }

    protected function createTenant($id = 'tenant1')
    {
        // 1. CLEANUP: Paksa Hapus Database Fisik jika tertinggal (Zombie DB)
        // Nama default db: test_tenant_ + id (sesuai prefix di .env.testing)
        // Atau jika default tenancy config: tenant + id
        $prefix = config('tenancy.database.prefix', 'tenant');
        $dbName = $prefix . $id;
        
        // Drop database fisik secara manual SQL biar aman
        DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");

        // 2. Create Tenant
        $tenant = Tenant::create([
            'id' => $id
        ]);

        // 3. Create Domain
        $baseUrl = config('app.url_base', 'localhost');
        $tenant->domains()->create([
            'domain' => $id . '.' . $baseUrl
        ]);

        return $tenant;
    }

    // Helper Baru: Dapatkan URL Tenant untuk HTTP Request
    protected function tenantUrl($tenant, $uri)
    {
        $domain = $tenant->domains->first()->domain;
        return "http://{$domain}" . $uri;
    }
}