<?php

use Illuminate\Http\Request;
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

Route::group(["prefix" => "v1", "middleware" => "update_last_seen"], function () {
    Route::post("register", [\App\Http\Controllers\Api\V1\AuthController::class, "register"]);
    Route::post("login", [\App\Http\Controllers\Api\V1\AuthController::class, "login"]);

    Route::group(["middleware" => "auth:sanctum"], function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::get("friends/explore", [\App\Http\Controllers\Api\V1\ContactController::class, "explore"]);
        Route::get("friends", [\App\Http\Controllers\Api\V1\ContactController::class, "contacts"]);
        Route::post("friends/add", [\App\Http\Controllers\Api\V1\ContactController::class, "add"]);
        Route::post("friends/accept", [\App\Http\Controllers\Api\V1\ContactController::class, "accept"]);
        Route::post("friends/reject", [\App\Http\Controllers\Api\V1\ContactController::class, "reject"]);
    });
});
