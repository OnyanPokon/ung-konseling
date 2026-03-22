<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HariLayanan\HariLayananController;
use App\Http\Controllers\JadwalKonselor\JadwalKonselorController;
use App\Http\Controllers\Konselis\KonselisController;
use App\Http\Controllers\Konselor\KonselorController;
use App\Http\Controllers\SesiKonseling\SesiKonselingController;
use App\Http\Controllers\Tiket\TiketController;
use App\Http\Controllers\Ai\AiController;
use App\Http\Controllers\Artikel\ArtikelController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/landing/artikel/', [ArtikelController::class, 'landing']);
Route::get('/landing/artikel/{slug}', [ArtikelController::class, 'detail']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::get("me", "getUser");
        Route::post("logout", "logout");
    });

    Route::prefix('konseli')->controller(KonselisController::class)->group(function () {
        Route::get("/", "index");
        Route::get("/user/{id}", "getByUserId");
        Route::post("/", "store");
        Route::delete("/multi-delete", "multiDestroy");
        Route::get("/{id}", "show");
        Route::put("/{id}", "update");
        Route::delete("/{id}", "destroy");
    });

    Route::prefix('konselor')->controller(KonselorController::class)->group(function () {
        Route::get("/", "index");
        Route::get("/user/{id}", "getByUserId");
        Route::post("/", "store");
        Route::delete("/multi-delete", "multiDestroy");
        Route::get("/{id}", "show");
        Route::put("/{id}", "update");
        Route::delete("/{id}", "destroy");
    });

    Route::prefix('hari_layanan')->controller(HariLayananController::class)->group(function () {
        Route::get("/", "index");
        Route::get("/konselor/{id}", "hariByKonselor");
        Route::post("/", "store");
        Route::delete("/multi-delete", "multiDestroy");
        Route::get("/{id}", "show");
        Route::put("/{id}", "update");
        Route::delete("/{id}", "destroy");
    });

    Route::prefix('jadwal_konselor')->controller(JadwalKonselorController::class)->group(function () {
        Route::get("/", "index");
        Route::post("/", "store");
        Route::delete("/multi-delete", "multiDestroy");
        Route::get("/{id}", "show");
        Route::put("/{id}", "update");
        Route::delete("/{id}", "destroy");
    });

    Route::prefix('tiket')->controller(TiketController::class)->group(function () {
        Route::get("/", "index");
        Route::post("/", "store");
        Route::delete("/multi-delete", "multiDestroy");
        Route::get("/{id}", "show");
        Route::put("/{id}", "update");
        Route::delete("/{id}", "destroy");
    });

    Route::prefix('artikel')->controller(ArtikelController::class)->group(function () {
        Route::get("/", "index");
        Route::post("/", "store");
        Route::delete("/multi-delete", "multiDestroy");
        Route::get("/{id}", "show");
        Route::put("/{id}", "update");
        Route::delete("/{id}", "destroy");
    });



    Route::prefix('sesi_konseling')->controller(SesiKonselingController::class)->group(function () {
        Route::get("/", "index");
        Route::post("/", "store");
        Route::delete("/multi-delete", "multiDestroy");
        Route::get("/{id}", "show");
        Route::put("/{id}", "update");
        Route::delete("/{id}", "destroy");
    });

    Route::post('/ai-chat', [AiController::class, 'chat']);

    Route::get('/notifications', [NotificationController::class, 'index']);

    Route::get('/notifications/unread', [NotificationController::class, 'unread']);

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});
Route::prefix("auth")->controller(AuthController::class)->group(function () {
    Route::post("login", "login")->name('login');
});

Route::post('register/konseli', [KonselisController::class, 'register']);
