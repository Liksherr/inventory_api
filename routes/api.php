<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/health', function () {
    DB::connection()->getPdo();

    if (! Schema::hasTable('products')) {
        return response()->json([
            'status' => 'unavailable',
            'message' => 'Tabel produk belum tersedia. Jalankan php artisan migrate.',
        ], 503);
    }

    return response()->json([
        'status' => 'ok',
        'message' => 'API dan database siap digunakan.',
    ]);
});

Route::apiResource('products', ProductController::class);
