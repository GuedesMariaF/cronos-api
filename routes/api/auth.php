<?php

use App\Http\Controllers\Auth\Auth;
use App\Http\Controllers\Auth\SocialAuth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([], function () {
    Route::post("/login", [Auth::class, "login"]);
    Route::post("/refresh-token", [Auth::class, "refreshToken"]);

    Route::get("/google", [SocialAuth::class, "redirectToGoogle"]);
    Route::get("/google/callback", [SocialAuth::class, "handleGoogleCallback"]);
});

Route::middleware(['auth.api'])->group(function () {
    Route::get("/me", [Auth::class, "me"]);
});
