<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User; // <--- PENTING: Pakai Model Tenant
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register Customer (User Biasa)
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users', // Unique di tabel users TENANT
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // Default role adalah customer
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil! Selamat berbelanja.',
            'token' => $user->createToken('tenant_token')->plainTextToken,
            'user' => $user
        ], 201);
    }

    // Login (Bisa untuk Admin Toko maupun Customer)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari user di database TENANT yang sedang aktif
        $user = User::where('email', $request->email)->first();

        // Validasi Password
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Generate Token
        // Kita bisa menambah 'ability' berdasarkan role jika mau
        $tokenAbilities = $user->role === 'admin' ? ['admin-access'] : ['customer-access'];
        
        $token = $user->createToken('tenant_token', $tokenAbilities)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil di toko ini.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role, // Penting untuk Frontend (Vue) membedakan menu
            ]
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        // Menghapus token yang sedang dipakai
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil logout dari toko.']);
    }

    // Cek Profile (Untuk memastikan user login siapa)
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'role' => $request->user()->role,
            'store' => tenant('id') // Info tambahan kita sedang di toko mana
        ]);
    }
}