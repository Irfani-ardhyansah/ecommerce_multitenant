<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains')->latest()->paginate(10);
        return response()->json($tenants);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|alpha_dash|unique:tenants,id', // ID tidak boleh spasi & harus unik
            'domain' => 'required|alpha_dash', // Subdomain saja (misal: 'tokobudi')
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        $baseUrl = config('app.url_base', 'localhost'); // Setup ini nanti di .env
        $fullDomain = $request->domain . '.' . $baseUrl;

        // Cek domain availability di tabel domains
        if (\App\Models\Tenant::whereHas('domains', fn($q) => $q->where('domain', $fullDomain))->exists()) {
            throw ValidationException::withMessages(['domain' => 'Domain already taken.']);
        }

        $tenant = Tenant::create([
            'id' => $request->id,
        ]);

        $tenant->domains()->create([
            'domain' => $fullDomain
        ]);

        $tenant->run(function () use ($request) {
            \App\Models\Tenant\User::create([
                'name' => 'Admin Owner',
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => 'admin'
            ]);
        });

        return response()->json([
            'message' => 'Tenant created successfully. Database generated.',
            'tenant_id' => $tenant->id,
            'domain' => $fullDomain,
            'login_url' => "http://{$fullDomain}/login"
        ], 201);
    }

    public function destroy($id)
    {
        $tenant = Tenant::find($id);

        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }

        $tenant->delete();

        return response()->json([
            'message' => "Tenant '{$id}' has been deleted and its database dropped."
        ]);
    }
}