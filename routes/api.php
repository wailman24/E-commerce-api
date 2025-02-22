<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\EmailVerificationController;

//use App\Http\Controllers\Auth\AuthController;


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::delete('/delete/{id}', [UserController::class, 'delete']);


Route::middleware(['auth:sanctum', 'isClient'])->group(function () {
    Route::post('/addseller', [SellerController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    Route::get('/getallseller', [SellerController::class, 'index']);
    Route::put('/sellerstatus/{id}', [SellerController::class, 'updatestatus']);
    Route::delete('/deleteseller/{id}', [SellerController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Email verification route
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');
    // To Logout Route
    Route::post('/logout', [UserController::class, 'logout']);
    // Resend email verification route
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->name('verification.resend');
});

Route::middleware(['auth:sanctum','isClientOrSeller'])->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'view_wishlist']);
    
    Route::delete('/wishlist/remove', [WishlistController::class, 'remove_from_wishlist']);
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{reviewId}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy']);
});

Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);

Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist']);