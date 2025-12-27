<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Halaman Pusat';
});

Route::get('/test', function () {
    return 'Test Page';
});