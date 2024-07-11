<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\OrderProductController;
use App\Http\Controllers\ProductChangeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TelegramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WoocommerceController;


Route::group(['middleware' => ['auth', 'verify', 'superAdmin', 'role']], function () {

    Route::get('/users', [UserController::class, 'show'])->name('manageUsers');
    Route::get('/confirm_user/{id}', [UserController::class, 'confirm']);
    Route::get('/suspend_user/{id}', [UserController::class, 'suspend']);
    Route::get('/add_user', [UserController::class, 'addUser']);
    Route::post('/add_user', [UserController::class, 'insertUser']);
    Route::get('/edit_user/{id}', [UserController::class, 'edit']);
    Route::post('/edit_user/{id}', [UserController::class, 'update']);

    Route::post('/deposit/changeConfirm/{id}', [DepositController::class, 'changeConfirm']);

    Route::get('settings', [SettingController::class, 'showSettings'])->name('settings');
    Route::post('settings', [SettingController::class, 'editSettings']);

    Route::get('coupons', [CouponController::class, 'couponList'])->name('couponList');
    Route::get('coupon/add', [CouponController::class, 'newForm'])->name('addCoupon');
    Route::post('coupon/add', [CouponController::class, 'storeNew']);
    Route::get('coupon/edit/{id}', [CouponController::class, 'editForm']);
    Route::post('coupon/edit/{id}', [CouponController::class, 'update']);
    Route::post('coupon/delete/{id}', [CouponController::class, 'deleteCoupon']);

    Route::get('statistic', [OrderProductController::class, 'showStatistic'])->name('statistic');

    Route::get('customers_deposit_list', [CustomerController::class, 'customersDepositList'])->name('customersDepositList');
    Route::get('customers_order_list', [CustomerController::class, 'customersOrderList'])->name('customersOrderList');
    Route::post('approveDeposit/{id}', [CustomerController::class, 'approveDeposit']);
    Route::post('rejectDeposit/{id}', [CustomerController::class, 'rejectDeposit']);
    Route::post('approveOrder/{id}', [CustomerController::class, 'approveOrder']);
    Route::post('rejectOrder/{id}', [CustomerController::class, 'rejectOrder']);
});

Route::group(['middleware' => ['auth', 'verify']], function () {

    Route::get('/', [UserController::class, 'home']);
    Route::get('/edit_user', [UserController::class, 'edit'])->name('editUser');
    Route::post('/edit_user', [UserController::class, 'update']);

});

Route::group(['middleware' => ['auth', 'verify', 'superAdmin', 'admin', 'safir', 'role']], function () {

    Route::get('add_order', [OrderController::class, 'newForm'])->name('newOrder');
    Route::post('add_order', [OrderController::class, 'insertOrder']);

    Route::get('edit_order/{id}', [OrderController::class, 'editForm']);
    Route::post('edit_order/{id}', [OrderController::class, 'update']);

    Route::post('delete_order/{id}', [OrderController::class, 'deleteOrder']);

    Route::get('/customers', [CustomerController::class, 'customersList'])->name('CustomerList');
    Route::get('/customer/add', [CustomerController::class, 'addForm'])->name('newCustomer');
    Route::post('/customer/add', [CustomerController::class, 'storeNewCustomer']);
    Route::post('/customer/delete/{id}', [CustomerController::class, 'deleteCustomer']);
    Route::get('/customer/edit/{id}', [CustomerController::class, 'showEditForm']);
    Route::post('/customer/edit/{id}', [CustomerController::class, 'updateCustomer']);

});

Route::group(['middleware' => ['auth', 'verify', 'superAdmin', 'admin', 'safir', 'print', 'role']], function () {

    Route::get('orders', [OrderController::class, 'showOrders'])->name('listOrders');
    Route::post('/invoice/{id}', [OrderController::class, 'invoice']);
    Route::post('/orders/dateFilter', [OrderController::class, 'dateFilter']);

    Route::post('/viewOrder/{id}', [OrderController::class, 'viewOrder']);



});

Route::group(['middleware' => ['auth', 'verify', 'superAdmin', 'safir', 'role']], function () {

    Route::get('/deposits', [DepositController::class, 'depositList'])->name('DepositList');
    Route::get('/deposit/add', [DepositController::class, 'newForm'])->name('addDeposit');
    Route::post('/deposit/add', [DepositController::class, 'storeNew']);
    Route::post('/deposit/delete/{id}', [DepositController::class, 'deleteDeposit']);
    Route::get('/deposit/edit/{id}', [DepositController::class, 'editDeposit']);
    Route::post('/deposit/edit/{id}', [DepositController::class, 'updateDeposit']);

});

Route::group(['middleware' => ['auth', 'verify', 'superAdmin', 'print', 'role']], function () {

    Route::post('change_state/{id}', [OrderController::class, 'changeState']);
    Route::post('/set_send_method/{id}', [OrderController::class, 'setSendMethod']);

    Route::post('pdf/{id}', [OrderController::class, 'pdf']);
    Route::get('pdfs/{ids}', [OrderController::class, 'pdfs']);
});

Route::group(['middleware' => ['auth', 'verify', 'superAdmin', 'admin', 'role']], function () {

    Route::post('confirm_invoice/{id}', [OrderController::class, 'confirmInvoice']);
    Route::post('cancel_invoice/{id}', [OrderController::class, 'cancelInvoice']);

    Route::get('/customer/transaction/{id}', [CustomerController::class, 'customersTransactionList'])
        ->name('customersTransactionList');
    Route::get('/customer/SOA/{id}', [CustomerController::class, 'customerSOA']);
    Route::get('/customerDeposit/add/{id}', [CustomerController::class, 'newForm']);
    Route::get('/customerDeposit/add/{id}/{linkId}', [CustomerController::class, 'newForm']);
    Route::post('/customerDeposit/add/{id}', [CustomerController::class, 'storeNew']);
    Route::post('/customerDeposit/delete/{id}', [CustomerController::class, 'deleteDeposit']);

    Route::post('/orders/paymentMethod/{id}', [OrderController::class, 'paymentMethod']);
});

//**************************************  superAdmin  *****  warehouse  *******************

Route::group(['middleware' => ['auth', 'verify', 'superAdmin', 'warehouse', 'role']], function () {

    Route::get('products', [ProductController::class, 'showProducts'])
        ->name('productList');
    Route::get('product/add', [ProductController::class, 'showAddForm'])
        ->name('addProduct');
    Route::post('product/add', [ProductController::class, 'storeNew']);
    Route::get('product/edit/{id}', [ProductController::class, 'showEditForm']);
    Route::post('product/edit/{id}', [ProductController::class, 'editProduct']);
    Route::post('product/deletePhoto/{id}', [ProductController::class, 'deletePhoto']);
    Route::post('product/delete/{id}', [ProductController::class, 'deleteProduct']);

    Route::get('/productQuantity/add/{id}', [ProductChangeController::class, 'addQuantity']);
    Route::post('/productQuantity/add/{id}', [ProductChangeController::class, 'insertRecord']);
    Route::get('/productQuantity/delete/{id}', [ProductChangeController::class, 'deleteRecord']);

});

//**************************************  safir  *************************************

Route::group(['middleware' => ['auth', 'verify', 'safir', 'role']], function () {

    Route::get('/transactions', [TransactionController::class, 'show'])->name('transactions');
});

Route::post('/woocommerce/{website}', [WoocommerceController::class, 'addWebsiteOrder']);
Route::get('/woocommerce/{website}', [WoocommerceController::class, 'addWebsiteOrder']);
Route::get('/backup', [TelegramController::class, 'backUpDatabase']);

Route::get('/product/alarm', [ProductChangeController::class, 'productAlarm']);

Route::get('/list', [ProductController::class, 'productsList']);

Route::get('/clear/route', [SettingController::class, 'clearRoute']);

Route::get('/command', [SettingController::class, 'command']);


require __DIR__ . '/auth.php';
