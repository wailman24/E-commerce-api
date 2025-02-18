<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailVerificationController;
//use App\Http\Controllers\Auth\AuthController;


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::delete('/delete/{id}', [UserController::class, 'delete']);




Route::middleware('auth:sanctum')->group(function () {
    // Email verification route
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    // Resend email verification route
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->name('verification.resend');
});
