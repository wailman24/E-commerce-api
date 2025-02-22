<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;

use App\Http\Controllers\WishlistController;
use App\Http\Controllers\OrderController;

use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategorieController;
//use App\Http\Controllers\Auth\AuthController;


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::delete('/delete/{id}', [UserController::class, 'delete']);

Route::middleware(['auth:sanctum', 'isSeller'])->group(function () {
    Route::post('/updateseller/{id}', [SellerController::class, 'updateseller']);



    Route::post('/addimage', [ImageController::class, 'store']);
    Route::post('/updateimage/{Image}', [ImageController::class, 'updateimage']);
    Route::delete('/deleteimage/{Image}', [ImageController::class, 'destroy']);

    Route::post('/addproduct', [ProductController::class, 'store']);
    Route::put('/updateproduct/{Product}', [ProductController::class, 'update']);
    Route::delete('/deleteproduct/{Product}', [ProductController::class, 'destroy']);

});
Route::middleware(['auth:sanctum', 'isClient'])->group(function () {

    Route::post('/addseller', [SellerController::class, 'store']);
});


Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {

    Route::get('/getallseller', [SellerController::class, 'index']);
    Route::put('/sellerstatus/{id}', [SellerController::class, 'updatestatus']);

    Route::post('/addcategory', [CategorieController::class, 'store']);
    Route::put('/updatecategory/{Category}', [CategorieController::class, 'update']);
    Route::delete('/deletecategory/{Category}', [CategorieController::class, 'destroy']);

    Route::put('/productstatus/{Product}', [ProductController::class, 'updatestatus']);
    Route::delete('/deleteproductadmin/{Product}', [ProductController::class, 'destroy']);

    Route::delete('/deleteimageadmin/{Image}', [ImageController::class, 'destroy']);


});

Route::get('/getallcategory', [CategorieController::class, 'index']);
Route::get('/getcategory/{Category}', [CategorieController::class, 'show']);

Route::get('/getallproduct', [ProductController::class, 'index']);
Route::get('/getproduct/{Product}', [ProductController::class, 'show']);

Route::get('/getallimage', [ImageController::class, 'index']);
Route::get('/getimage/{Image}', [ImageController::class, 'show']);

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

/*  */
