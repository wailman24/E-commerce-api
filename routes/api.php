<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;

use App\Http\Controllers\WishlistController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\EmailVerificationController;

use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\OtpController;
//use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PaymentController;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::delete('/delete/{id}', [UserController::class, 'delete']);

Route::middleware(['auth:sanctum', 'isSeller'])->group(function () {
    Route::post('/updateseller/{id}', [SellerController::class, 'updateseller']);

    Route::post('/addimage', [ImageController::class, 'store']);
    Route::post('/updateimage/{Image}', [ImageController::class, 'updateimage']);
    Route::delete('/deleteimage/{Image}', [ImageController::class, 'destroy']);

    Route::get('/getallselleritems', [OrderItemController::class, 'getallselleritems']);

    Route::get('/getallproductsforsellers', [ProductController::class, 'getallproductsforsellers']);
    Route::get('/getnotvalidproductforseller', [ProductController::class, 'getnotvalidproductforseller']);
    Route::post('/addproduct', [ProductController::class, 'store']);
    Route::put('/updateproduct/{Product}', [ProductController::class, 'update']);
    Route::delete('/deleteproduct/{Product}', [ProductController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'isClient'])->group(function () {

    Route::post('/payment', [PaymentController::class, 'createPayment'])->name('payment');

    ///////

    Route::post('/addseller', [SellerController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {


    Route::post('/payout/{seller}', [PaymentController::class, 'payoutToSeller']);

    //////

    Route::get('/getallseller', [SellerController::class, 'index']);
    Route::put('/sellerstatus/{id}', [SellerController::class, 'updatestatus']);
    Route::delete('/deleteseller/{id}', [SellerController::class, 'destroy']);
    Route::get('/getallproducts', [ProductController::class, 'getallproducts']);

    ///////////////////////////////////////////////////

    Route::get('/order', [OrderController::class, 'index']);

    //////////////////////////////////////////////////

    Route::post('/addcategory', [CategorieController::class, 'store']);
    Route::put('/updatecategory/{Category}', [CategorieController::class, 'update']);
    Route::delete('/deletecategory/{Category}', [CategorieController::class, 'destroy']);

    Route::put('/productstatus/{Product}', [ProductController::class, 'updatestatus']);
    Route::delete('/deleteproductadmin/{Product}', [ProductController::class, 'destroy']);

    Route::delete('/deleteimageadmin/{Image}', [ImageController::class, 'destroy']);

    /////////////////////////////////////////////////

    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'isClientOrSeller'])->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'view_wishlist']);
    Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist']);
    //Route::delete('/wishlist/remove', [WishlistController::class, 'remove_from_wishlist']);
    Route::get('/existinwishlist/{product}', [WishlistController::class, 'is_in_wishlist']);
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{reviewId}', [ReviewController::class, 'update']);

    ////////////////////////////////////////////////

    Route::get('/order_item', [OrderItemController::class, 'index']);
    Route::post('/order_item', [OrderItemController::class, 'store']);
    Route::put('/order_item/{order_item}', [OrderItemController::class, 'update']);
    Route::delete('/order_item/{order_item}', [OrderItemController::class, 'destroy']);
    Route::get('/order_item/{product_id}', [OrderItemController::class, 'is_in_cart']);
    /// inc and dec ///

    Route::put('/dec/{order_item}', [OrderItemController::class, 'dec']);
    Route::put('/inc/{order_item}', [OrderItemController::class, 'inc']);

    ///////////////////

});


Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);

/////////////////////////////////////////////////


Route::get('/getallcategory', [CategorieController::class, 'getall']);
Route::get('/getcategory/{Category}', [CategorieController::class, 'show']);

Route::get('/getvalidproducts', [ProductController::class, 'getvalidproducts']);
Route::get('/getproduct/{Product}', [ProductController::class, 'show']);

Route::get('/getallimage', [ImageController::class, 'index']);
Route::get('/getimage/{Image}', [ImageController::class, 'show']);

/////////////////////////////////////////////////

//payment
Route::get('/payment/success', [PaymentController::class, 'Success'])->name('payment.success');
Route::get('/payment/cancel', [PaymentController::class, 'Cancel'])->name('payment.cancel');

//Route::post('/send-otp', [OtpController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    /// get the authenticatided user
    Route::get('/getuser', [UserController::class, 'getuser']);

    Route::get('/getBestDealsProducts', [ProductController::class, 'getBestDealsProducts']);
});

/*  */
