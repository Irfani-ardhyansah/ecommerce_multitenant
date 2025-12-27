<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\AuthController;
use App\Http\Controllers\Central\TenantController;


Route::post('/central/register', [AuthController::class, 'register']);
Route::post('/central/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/central/logout', [AuthController::class, 'logout']);
    Route::get('/central/me', [AuthController::class, 'me']);

    Route::get('/central/tenants', [TenantController::class,'index']);
    Route::post('/central/tenants', [TenantController::class,'store']);
    Route::delete('/central/tenants/{id}', [TenantController::class,'destroy']);
});