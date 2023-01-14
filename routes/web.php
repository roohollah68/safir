<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\OrderProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WoocommerceController;



Route::group(['middleware'=>['auth' , 'verify'] ],function (){

//  Order
    Route::get('add_order',[OrderController::class , 'newForm'])->name('newOrder');
    Route::post('add_order',[OrderController::class , 'insertOrder']);



    Route::get('edit_order/{id}',[OrderController::class , 'editForm']);
    Route::post('edit_order/{id}',[OrderController::class , 'editOrder']);

    Route::post('get_orders',[OrderController::class , 'getOrders']);
    Route::get('orders',[OrderController::class , 'showOrders'])->name('listOrders');

    Route::post('delete_order/{id}',[OrderController::class , 'deleteOrder']);

    Route::post('send_to_telegram/{id}',[TelegramController::class , 'sendOrderToTelegramById']);

//  User
    Route::get('/',[UserController::class , 'home']);
    Route::get('/users',[UserController::class , 'show'])->name('manageUsers');
    Route::get('/confirm_user/{id}',[UserController::class , 'confirm']);
    Route::get('/suspend_user/{id}',[UserController::class , 'suspend']);
    Route::get('/edit_user/{id}',[UserController::class , 'edit']);
    Route::get('/edit_user',[UserController::class , 'edit'])->name('editUser');
    Route::post('/edit_user/{id}',[UserController::class , 'update']);
    Route::post('/edit_user',[UserController::class , 'update']);

//  Customer
    Route::get('/customers',[CustomerController::class , 'customersList'])->name('CustomerList');
    Route::get('/customer/add',[CustomerController::class , 'addForm'])->name('newCustomer');
    Route::post('/customer/add',[CustomerController::class , 'storeNewCustomer']);
    Route::post('/customer/delete/{id}',[CustomerController::class , 'deleteCustomer']);
    Route::get('/customer/edit/{id}',[CustomerController::class , 'showEditForm']);
    Route::post('/customer/edit/{id}',[CustomerController::class , 'updateCustomer']);

//  Deposit
    Route::get('/deposits' , [DepositController::class , 'depositList'])->name('DepositList');
    Route::get('/deposit/add' , [DepositController::class , 'newForm'])->name('addDeposit');
    Route::post('/deposit/add' , [DepositController::class , 'storeNew']);
    Route::post('/deposit/delete/{id}' , [DepositController::class , 'deleteDeposit']);
    Route::get('/deposit/edit/{id}' , [DepositController::class , 'editDeposit']);
    Route::post('/deposit/edit/{id}' , [DepositController::class , 'updateDeposit']);
    Route::post('/deposit/changeConfirm/{id}' , [DepositController::class , 'changeConfirm']);

//  Transaction
    Route::get('/transactions' , [TransactionController::class , 'show'])->name('transactions');
});

Route::group(['middleware'=>['auth','admin']],function (){
    Route::get('products', [ProductController::class , 'showProducts'])
        ->name('productList');
    Route::get('product/add', [ProductController::class , 'showAddForm'])
        ->name('addProduct');
    Route::post('product/add', [ProductController::class , 'storeNew']);
    Route::get('product/edit/{id}', [ProductController::class , 'showEditForm']);
    Route::post('product/edit/{id}', [ProductController::class , 'editProduct']);
    Route::post('product/deletePhoto/{id}', [ProductController::class , 'deletePhoto']);
    Route::post('product/delete/{id}', [ProductController::class , 'deleteProduct']);

    Route::get('settings' , [SettingController::class , 'showSettings'])->name('settings');
    Route::post('settings' , [SettingController::class , 'editSettings']);

    Route::get('coupons' , [CouponController::class , 'couponList'])->name('couponList');
    Route::get('coupon/add' , [CouponController::class , 'newForm'])->name('addCoupon');
    Route::post('coupon/add' , [CouponController::class , 'storeNew']);
    Route::get('coupon/edit/{id}' , [CouponController::class , 'editForm']);
    Route::post('coupon/edit/{id}' , [CouponController::class , 'update']);
    Route::post('coupon/delete/{id}', [CouponController::class , 'deleteCoupon']);

    Route::post('increase_state/{id}',[OrderController::class , 'increaseState']);

    Route::get('statistic' , [OrderProductController::class , 'show'])->name('statistic');
});


Route::post('/telegram',[TelegramController::class , 'receive']);
Route::get('register-from-telegram',[RegisteredUserController::class , 'fromTelegram']);
Route::get('list-orders/{id}/{pass}',[OrderController::class , 'listOrderTelegram']);
Route::get('new-order/{id}/{pass}',[OrderController::class , 'newOrderTelegram']);
Route::get('new-order-receipt/{id}/{pass}/{file_id}',[OrderController::class , 'newOrderWithPhotoTelegram']);

Route::post('/deposit/telegram' , [DepositController::class , 'receive']);
Route::get('/deposit/add/{id}/{pass}/{file_id}' , [DepositController::class , 'newDepositWithPhotoTelegram']);

Route::post('/woocommerce/{website}' , [WoocommerceController::class , 'addPeptinaOrder']);



require __DIR__.'/auth.php';
