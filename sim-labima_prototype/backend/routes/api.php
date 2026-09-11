<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CabangController;
use App\Http\Controllers\Api\MasterMitraController;
use App\Http\Controllers\Api\OdcController;
use App\Http\Controllers\Api\OdpController;
use App\Http\Controllers\Api\ProdukController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::get('/cabang', [CabangController::class, 'index']);
Route::prefix('cabang')->group(function () {
    Route::post('/', [CabangController::class, 'store']);
    Route::get('/{id}', [CabangController::class, 'show']);
    Route::put('/{id}', [CabangController::class, 'update']);
    Route::delete('/{id}', [CabangController::class, 'destroy']);
});

Route::get("/produk", [ProdukController::class, "index"]);
Route::prefix("produk")->group(function () {
    Route::post("/", [ProdukController::class, "store"]);
    Route::get("/{id}", [ProdukController::class, "show"]);
    Route::put("/{id}", [ProdukController::class, "update"]);
    Route::delete("/{id}", [ProdukController::class, "destroy"]);
});

Route::get("/mitra", [MasterMitraController::class, "index"]);
Route::prefix("mitra")->group(function () {
    Route::post("/", [MasterMitraController::class, "store"]);
    Route::get("/{id}", [MasterMitraController::class, "show"]);
    Route::put("/{id}", [MasterMitraController::class, "update"]);
    Route::delete("/{id}", [MasterMitraController::class, "destroy"]);
});

Route::get("/odc", [OdcController::class,"index"]);
Route::prefix("odc")->group(function () {
    Route::post("/", [OdcController::class, "store"]);
    Route::get("/{id}", [OdcController::class, "show"]);
    Route::put("/{id}", [OdcController::class, "update"]);
    Route::delete("/{id}", [OdcController::class, "destroy"]);
});

Route::get("/odp", [OdpController::class,"index"]);
Route::prefix("odp")->group(function () {
    Route::post("/", [OdpController::class, "store"]);
    Route::get("/{id}", [OdpController::class, "show"]);
    Route::put("/{id}", [OdpController::class, "update"]);
    Route::delete("/{id}", [OdpController::class, "destroy"]);
});