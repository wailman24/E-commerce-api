<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\EmailVerificationController;

//use App\Http\Controllers\Auth\AuthController;


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::delete('/delete/{id}', [UserController::class, 'delete']);

Route::middleware(['auth:sanctum', 'isSeller'])->group(function () {
    Route::post('/updateseller/{id}', [SellerController::class, 'updateseller']);
});
Route::middleware(['auth:sanctum', 'isClient'])->group(function () {

    Route::post('/addseller', [SellerController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {

    Route::get('/getallseller', [SellerController::class, 'index']);
    Route::put('/sellerstatus/{id}', [SellerController::class, 'updatestatus']);
    Route::delete('/deleteseller/{id}', [SellerController::class, 'destroy']);

    Route::get('/order', [OrderController::class, 'index']);
});



Route::middleware('auth:sanctum')->group(function () {
    // Email verification route
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    // Resend email verification route
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->name('verification.resend');


});


// Route::apiResource('/order_item', OrderItemController::class);
Route::middleware(['auth:sanctum', 'isClient'])->group(function () {
    Route::get('/order_item', [OrderItemController::class, 'index']);
    Route::post('/order_item', [OrderItemController::class, 'store']);
    Route::put('/order_item/{order_item}', [OrderItemController::class, 'update']);
    Route::delete('/order_item/{order_item}', [OrderItemController::class, 'destroy']);
});
