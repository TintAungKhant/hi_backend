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

// TODO:: change proper http verbs

Route::group(["prefix" => "v1", "middleware" => "update_last_seen"], function () {
    Broadcast::routes(['middleware' => ['auth:sanctum']]);
    Route::post("register", [\App\Http\Controllers\Api\V1\AuthController::class, "register"]);
    Route::post("login", [\App\Http\Controllers\Api\V1\AuthController::class, "login"]);
    Route::any("logout", [\App\Http\Controllers\Api\V1\AuthController::class, "logout"]);

    Route::group(["middleware" => "auth:sanctum"], function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::get("friends/explore", [\App\Http\Controllers\Api\V1\ContactController::class, "explore"]);
        Route::get("friends", [\App\Http\Controllers\Api\V1\ContactController::class, "contacts"]);
        Route::post("friends/{user_id}/add", [\App\Http\Controllers\Api\V1\ContactController::class, "add"]);
        Route::post("friends/{user_id}/accept", [\App\Http\Controllers\Api\V1\ContactController::class, "accept"]);
        Route::post("friends/{user_id}/delete", [\App\Http\Controllers\Api\V1\ContactController::class, "delete"]);

        Route::get("profile/{user_id?}", [\App\Http\Controllers\Api\V1\ProfileController::class, "get"]);
        Route::post("profile", [\App\Http\Controllers\Api\V1\ProfileController::class, "update"]);
        Route::post("profile/image", [\App\Http\Controllers\Api\V1\ProfileController::class, "updateProfileImage"]);

        Route::get("conversations", [\App\Http\Controllers\Api\V1\ConversationController::class, "get"]);
        Route::get("conversations/show", [\App\Http\Controllers\Api\V1\ConversationController::class, "show"]);
        Route::get("conversations/messages", [\App\Http\Controllers\Api\V1\MessageController::class, "get"]);
        Route::post("conversations/messages", [\App\Http\Controllers\Api\V1\MessageController::class, "store"]);
    });
});
