<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    /*Route::get('/wishlist', [WishlistController::class, 'view_wishlist']);
    Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist']);
    Route::delete('/wishlist/remove', [WishlistController::class, 'remove_from_wishlist']);
    Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{reviewId}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy']);*/
});
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
Route::put('/reviews/{reviewId}', [ReviewController::class, 'update']);
Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy']);
Route::get('/wishlist', [WishlistController::class, 'view_wishlist']);
Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist']);
Route::delete('/wishlist/remove', [WishlistController::class, 'remove_from_wishlist']);
