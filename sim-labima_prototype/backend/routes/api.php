<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CabangController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware(["auth:sanctum"])->group(function () {
    Route::get('/cabang', [CabangController::class, 'index']);
    Route::prefix('cabang')->group(function () {
        Route::post('/', [CabangController::class, 'store']);
        Route::get('/{id}', [CabangController::class, 'show']);
        Route::put('/{id}', [CabangController::class, 'update']);
        Route::delete('/{id}', [CabangController::class, 'destroy']);
    });
});
