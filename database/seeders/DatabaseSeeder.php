<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Wajib import ini
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat SUPER ADMIN (Central)
        User::firstOrCreate(
            ['email' => 'superadmin@central.com'],
            [
                'name' => 'Super Admin Platform',
                'password' => Hash::make('password'),
            ]
        );
        $this->command->info('Super Admin ready.');

        // 2. Daftar Tenant
        $tenants = [
            ['id' => 'tenant1', 'domain' => 'tenant1.localhost'],
            ['id' => 'tenant2', 'domain' => 'tenant2.localhost'],
        ];

        foreach ($tenants as $t) {
            $this->command->warn("Proses setup untuk {$t['id']}...");

            // ==========================================================
            // LANGKAH PEMBERSIHAN (ANTI ERROR DATABASE EXISTS)
            // ==========================================================
            
            // A. Hapus data di tabel tenants (jika ada)
            $oldTenant = Tenant::find($t['id']);
            if ($oldTenant) {
                $oldTenant->delete();
                $this->command->info("Data Tenant lama dihapus.");
            }

            // B. Hapus Database Fisik (Zombie) secara manual
            // Kita ambil prefix dari config (default: 'tenant')
            // Jadi namanya: tenanttenant1
            $dbName = config('tenancy.database.prefix') . $t['id']; 
            
            // Jalankan perintah SQL Drop Database
            DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
            $this->command->info("Database fisik {$dbName} berhasil di-drop (jika ada).");

            // ==========================================================
            // CREATE BARU
            // ==========================================================
            
            // C. Create Tenant Baru
            $tenant = Tenant::create(['id' => $t['id']]);
            
            // D. Create Domain
            $tenant->domains()->create(['domain' => $t['domain']]);

            $this->command->info("Tenant & Database baru dibuat.");

            // E. Seed Data
            sleep(1); // Jeda sebentar biar MySQL napas
            
            $tenant->run(function () {
                $this->call(TenantDatabaseSeeder::class);
            });
            
            $this->command->info("Data {$t['id']} selesai di-seed!");
            $this->command->info("------------------------------------------------");
        }
    }
}