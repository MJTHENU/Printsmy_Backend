<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\DesignController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ForgotPasswordController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::get('/users', [AuthController::class, 'index'])->middleware('auth:api')->name('users');
    Route::post('/register', [AuthController::class, 'register'])->middleware('auth:api')->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
    Route::put('/users/{id}', [AuthController::class, 'update']);
    Route::delete('/delete-user/{id}', [AuthController::class, 'delete']);
    Route::put('/users/{id}/password', [AuthController::class, 'updatePassword']);
    Route::put('/users/{id}/status', [AuthController::class, 'updateStatus']);
});



Route::middleware(['auth.jwt'])->group(function () {
        
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::post('/store-events', [EventController::class, 'store']);
    Route::put('events/{id}', [EventController::class, 'update']);
    Route::delete('events/{id}', [EventController::class, 'destroy']);
});

Route::middleware('jwt.verify')->group(function () {
    Route::post('/save-image', [DesignController::class, 'image']);
    Route::get('/design-img/{id}', [DesignController::class, 'getDesignImages']);
    Route::resource('event-designs', DesignController::class);
 
   Route::put('/event-design/{id}/logo', [DesignController::class, 'updateLogo']);
;
});



Route::post('/send-otp', [ForgotPasswordController::class, 'sendOTP']);
Route::post('/send-otp_with_phone', [ForgotPasswordController::class, 'sendOTPWithPhone']);
Route::post('/verify-account', [ForgotPasswordController::class, 'verifyOTP']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);




