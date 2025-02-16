<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\KeysunController;
use App\Http\Controllers\OrderProductController;
use App\Http\Controllers\ProductChangeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\ChequeController;
use App\Livewire\Counter;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WoocommerceController;


Route::middleware(['auth', 'verify'])->group(function () {

    Route::redirect('/', '/orders');

    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::get('/list', 'list')->name('usersList');
        Route::get('/confirm/{id}', 'confirm');
        Route::get('/suspend/{id}', 'suspend');
        Route::get('/delete/{id}', 'delete');
        Route::get('/add', 'add');
        Route::post('/add', 'insertUser');
        Route::get('/edit/{id?}', 'edit')->name('editUser');
        Route::post('/edit/{id?}', 'update');
    });

    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders', 'showOrders')->name('listOrders');
        Route::post('/orders/reload', 'getOrders');
        Route::get('add_order', 'newOrder')->name('newOrder');
        Route::post('add_order', 'insertOrder');
        Route::get('edit_order/{id}', 'editOrder');
        Route::post('edit_order/{id}', 'updateOrder');
        Route::post('delete_order/{id}', 'deleteOrder');
        Route::get('changeWarehouse/{orderId}/{warehouseId}', 'changeWarehose');
        Route::post('/viewOrder/{id}', 'viewOrder');
        Route::post('/confirmAuthorize/{id}', 'confirmAuthorize');
        Route::post('/invoice/{id}', 'invoice');
        Route::post('/orders/excel', 'excelData');
        Route::post('/orders/dateFilter', 'dateFilter');
        Route::get('/pdfs/{ids}', 'pdfs');
        Route::post('/orders/paymentMethod/{id}', 'paymentMethod');
        Route::post('cancel_invoice/{id}', 'cancelInvoice');
        Route::post('change_state/{id}/{state}', 'changeState');
        Route::post('/set_send_method/{id}', 'setSendMethod');
    });

    Route::controller(SettingController::class)->group(function () {
        Route::get('/invoiceData', 'invoiceData');
        Route::post('/invoiceData', 'invoiceDataSave');

        Route::get('settings', 'showSettings')->name('settings');
        Route::post('settings', 'editSettings');

        Route::get('/clear/route', 'clearRoute');
        Route::get('/command', 'command');
    });

    Route::controller(CommentController::class)->group(function () {
        Route::post('/viewComment/{id}', 'view');
        Route::post('/addComment/{id}', 'add');
    });

    ///STATISTIC
    Route::controller(StatisticController::class)->group(function () {
        Route::get('statistic', 'showStatistic')->name('statistic');
        Route::post('statistic', 'showStatistic')->name('statistic');
    });

    Route::controller(CustomerController::class)->group(function () {
        ///COUNTER
        Route::get('customers_deposit_list', 'customersDepositList')->name('customersDepositList');
        Route::get('customers_order_list', 'customersOrderList')->name('customersOrderList');
        Route::post('approveDeposit/{id}', 'approveDeposit');
        Route::post('rejectDeposit/{id}', 'rejectDeposit');
        Route::post('approveOrder/{id}', 'approveOrder');
        Route::post('rejectOrder/{id}', 'rejectOrder');

        ///CUSTOMER
        Route::get('/customers', 'customersList')->name('CustomerList');
        Route::get('/customer/add', 'addForm')->name('newCustomer');
        Route::post('/customer/add/{id?}', 'storeCustomer');
        Route::post('/customer/delete/{id}', 'deleteCustomer');
        Route::get('/customer/edit/{id}', 'showEditForm');
//        Route::post('/customer/edit/{id}', 'updateCustomer');
        Route::get('/changeTrust/{id}', 'changeTrust');
        Route::get('/blockList', 'blockList');
        Route::get('/changeBlock/{id}', 'changeBlock');

        ///CUSTOMER DEPOSIT
        Route::get('/customer/transaction/{id}', 'customersTransactionList')
            ->name('customersTransactionList');
        Route::get('/customer/SOA/{id}', 'customerSOA');
        Route::post('/customer/SOA/{id}', 'customerSOA');
        Route::get('/customerDeposit/add/{customerId}/{orderId?}', 'newForm');
        Route::get('/customerDeposit/edit/{customerId}/{depositId}', 'editForm');
        Route::post('/customerDeposit/addEdit/{customerId}/{orderId?}/{depositId?}', 'store');
        Route::post('/customerDeposit/delete/{id}', 'deleteDeposit');
        Route::post('/customerDeposit/view/{id}', 'viewDeposit');
        Route::get('/customerPaymentTracking', 'paymentTracking');
        Route::get('/postponedDay/{id}/{days}', 'postponedDay');

        ///PAYMENTLINK
        Route::get('/customer/depositLink/{id}', 'depositLink');
        Route::get('/customer/orderLink/{id}', 'orderLink');
        Route::post('/payLink/add/{transaction_id}/{order_id}', 'addPayLink');
        Route::post('/payLink/delete/{id}', 'deletePayLink');
    });

    Route::controller(DepositController::class)->prefix('deposit')->group(function () {
        ///SAFIR DEPOSIT
        Route::get('/list', 'depositList')->name('DepositList');
        Route::get('/add', 'newForm')->name('addDeposit');
        Route::post('/add', 'storeNew');
        Route::post('/delete/{id}', 'deleteDeposit');
        Route::get('/edit/{id}', 'editDeposit');
        Route::post('/edit/{id}', 'updateDeposit');
        Route::post('/changeConfirm/{id}', 'changeConfirm');
        Route::post('/safir/view/{id}', 'view');
    });

    Route::controller(CouponController::class)->prefix('coupon')->group(function () {
        ///SAFIR DISCOUNT
        Route::get('/list', 'couponList')->name('couponList');
        Route::get('/add', 'newForm')->name('addCoupon');
        Route::post('/add', 'storeNew');
        Route::get('/edit/{id}', 'editForm');
        Route::post('/edit/{id}', 'update');
        Route::post('/delete/{id}', 'deleteCoupon');
    });

    ///
    Route::get('/transactions', [TransactionController::class, 'show'])->name('transactions');

    Route::controller(ProductController::class)->group(function () {
        ///PRODUCT
        Route::get('products', 'showProducts')->name('productList');
        Route::get('product/add', 'showAddForm')->name('addProduct');
        Route::post('product/addOrEdit/{id?}', 'storeNew');
        Route::post('product/getData', 'getData');
        Route::post('addToProducts/{id}', 'addToProducts');
        Route::get('product/edit/{id}', 'showEditForm');
        Route::post('product/edit/{id}', 'editProduct');
        Route::post('product/deletePhoto/{id}', 'deletePhoto');
        Route::post('product/delete/{id}', 'deleteProduct');
        Route::get('product/fastEdit/{id}', 'fastEdit');

        Route::get('goods/management', 'goods');
        Route::get('/good/tag', 'tags');
        Route::post('/good/tag/{id}', 'saveTags');
        Route::get('production/schedule/{id}', 'production');
        Route::post('/product/change/available/{id}', 'changeAvailable');

        Route::get('warehouse/transfer', 'transfer');
        Route::post('warehouse/transfer', 'transferSave');
        Route::get('warehouse/manager', 'warehouseManager');
        Route::post('warehouse/manager', 'saveWarehouseManager');
    });

    Route::controller(ProductChangeController::class)->prefix('productQuantity')->group(function () {
        Route::get('/add/{id}', 'addQuantity');
        Route::post('/add/{id}', 'insertRecord');
        Route::get('/delete/{id}', 'deleteRecord');
    });

    ///REPORTS
    Route::controller(ReportController::class)->group(function () {
        Route::get('report/add', 'newReport');
        Route::post('report/add', 'saveReport');
        Route::get('report/list', 'list')->name('reportList');
        Route::post('commentResponse/{id}', 'response');
    });

    Route::controller(WithdrawalController::class)->prefix('Withdrawal')->group(function () {
        ///Withdrawal
        Route::get('/add', 'new')->name('addWithdrawal');
        Route::post('/add/{id?}', 'insertOrUpdate');
        Route::get('/edit/{id}', 'edit');
        Route::get('/list', 'list')->name('WithdrawalList');
        Route::post('/view/{id}', 'view');
        Route::post('/counterForm/{id}', 'counter');
        Route::post('/managerForm/{id}', 'manager');
        Route::post('/paymentForm/{id}', 'payment');
        Route::post('/recipientForm/{id}', 'recipient');
        Route::get('/tankhah/add', 'addTankhah');
        Route::get('/tankhah/edit/{id}', 'editTankhah');
        Route::post('/tankhah/add/{id?}', 'addEditTankhah');
    });

    Route::controller(BankTransactionController::class)->prefix('BankTransaction')->group(function () {
        ///BankManagement
        Route::get('/add', 'new')->name('addTransaction');
        Route::post('/addEdit/{id?}', 'insertOrUpdate');
        Route::get('/edit/{id}', 'edit');
        Route::get('/delete/{id}', 'delete');
        Route::get('/list', 'list')->name('BankTransactionList');
        Route::post('/view/{id}', 'view');
    });

    //CHEQUES
   Route::controller(ChequeController::class)->prefix('cheque')->group(function () {
        Route::get('/cheque', 'cheque')->name('chequeList');
        Route::post('/cheque', 'cheque')->name('chequeList');
        Route::get('/given/{id}', 'view')->name('cheque.view');
        Route::get('/received/{id}', 'receivedView')->name('cheque.view2');
        Route::post('/received/{id}', 'receivedView')->name('cheque.view2');
        Route::post('/pass', 'passCheque');
    });


    Route::controller(SupplierController::class)->prefix('Supplier')->group(function () {
        ///SUPPLIERS
        Route::get('/add', 'new');
        Route::post('/add', 'insertOrUpdate');
        Route::get('/edit/{id}', 'edit');
        Route::get('/list', 'list');
    });

    ///PROGRAMMER
    Route::get('/woocommerce/{website}', [WoocommerceController::class, 'addWebsiteOrder']);
    Route::get('/woocommerce', [WoocommerceController::class, 'viewFile']);

    ///Keysun
    Route::controller(KeysunController::class)->prefix('keysun')->group(function () {
        Route::get('/good','good');
    });

});

Route::post('/woocommerce/{website}', [WoocommerceController::class, 'addWebsiteOrder']);
Route::get('/backup', [TelegramController::class, 'backUpDatabase']);

Route::post('/sms' , [TelegramController::class, 'sms']);
Route::get('/sms' , [TelegramController::class, 'sms']);
require __DIR__ . '/auth.php';
