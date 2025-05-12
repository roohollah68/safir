<?php

use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\FormulationController;
use App\Http\Controllers\FixedCostController;
use App\Http\Controllers\KeysunController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\ProductChangeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionRequestController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\WoocommerceController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;


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
        Route::get('/accesslist', 'accesslist')->name('accessList');
        Route::get('/accesslist2', 'accesslist2')->name('accessList2');
        Route::get('/changeAccount/{id}', 'changeAccount');
        Route::post('/update-permission', 'accesslist')->name('updateUserPermission');
    });

    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders', 'showOrders')->name('listOrders');
        Route::post('/orders/reload', 'getOrders');
        Route::get('add_order', 'newOrder')->name('newOrder');
        Route::post('add_order/{id?}', 'insert');
        Route::get('edit_order/{id}', 'editOrder');
        Route::post('edit_order/{id}', 'insert');
        Route::post('delete_order/{id}', 'deleteOrder');
        Route::get('changeWarehouse/{orderId}/{warehouseId}', 'changeWarehose');
        Route::post('/viewOrder/{id}', 'viewOrder');
        Route::post('/confirmAuthorize/{id}', 'confirmAuthorize');
        Route::post('/invoice/{id}', 'invoice');
        Route::get('/invoiceView/{id}', 'invoiceView');
        Route::post('/orders/dateFilter', 'dateFilter');
        Route::get('/pdfs/{ids}', 'pdfs');
        Route::post('/orders/paymentMethod/{id}', 'paymentMethod');
        Route::post('cancel_invoice/{id}', 'cancelInvoice');
        Route::post('change_state/{id}/{state}', 'changeState');
        Route::post('/set_send_method/{id}', 'setSendMethod');
        Route::get('/history', 'orderHistory')->name('history');
        Route::post('/update-nu-records', 'updateNuRecords')->name('updateNuRecords');
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
        Route::get('productChart/{id}', 'productChart')->name('productChart');
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
        Route::get('/customer/CRM', 'CRM');
        Route::post('/customer/CRM', 'addCRM');
        Route::post('/customer/viewCRM/{id}', 'viewCRM');

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
        Route::post('/good/delete/{id}', 'deleteGood');
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
        Route::get('/add', 'new')->name('suppliers.new');
        Route::post('/add', 'insertOrUpdate');
        Route::get('/edit/{id}', 'edit');
        Route::get('/list', 'list');
    });

    ///PROGRAMMER
    Route::get('/woocommerce/{website}', [WoocommerceController::class, 'addWebsiteOrder']);
    Route::get('/woocommerce', [WoocommerceController::class, 'viewFile']);

    ///Keysun
    Route::controller(KeysunController::class)->prefix('keysun')->group(function () {
        Route::match(['post', 'get'], '/list', 'list')->name('TaxList');
        Route::get('/good', 'good');
        Route::post('/orders/excel', 'excelData');
        Route::get('/change/{id}', 'viewChange');
        Route::post('/import-excel', 'import');
        Route::get('/import-excel', 'importForm');
    });

    ///Formulation
    Route::controller(FormulationController::class)->prefix('formulation')->group(function () {
        Route::get('/list', 'list');
        Route::get('/add', 'add')->name('formulation.add');
        Route::get('/edit/{id}', 'edit')->name('formulation.edit');
        Route::post('/addEditRow/{id?}', 'addEditRow');
        Route::get('/deleteAll/{id}', 'deleteAll');
        Route::get('/deleteRow/{id}', 'deleteRow');
        Route::get('/view/{id}', 'view');
        Route::post('/getRawGoods/{id}', 'getRawGoods');
        Route::get('/raw-usage', 'rawUsage');
        Route::get('/formulation/exists/{goodId}', 'exists');
        Route::get('/production/formulationPDF', 'productionReport')->name('formulation.pdf');
    });

    //PRODUCTION REQUESTS
    Route::controller(ProductionRequestController::class)->prefix('productionRequest')->group(function () {
        Route::get('/create', 'create')->name('addEdit');
        Route::post('/store', 'store')->name('production.store');
        Route::get('/list', 'list')->name('productionList');
        Route::get('/edit/{id}', 'edit')->name('production.edit');
        Route::post('/update/{id}', 'update')->name('production.update');
        Route::delete('/{id}', 'delete')->name('production.delete');
    });

    // PRODUCTION
    Route::controller(ProductionController::class)->prefix('production')->group(function () {
        Route::get('/add', 'addProductionForm')->name('production.add.form');
        Route::post('/add', 'addProduction')->name('production.add');
    });

    // PROJECTS
    Route::controller(ProjectController::class)->prefix('projects')->group(function () {
        Route::get('/', 'index')->name('projectList');
        Route::get('/add', 'create')->name('project.add.form');
        Route::post('/add', 'storeProject')->name('project.add');
        Route::get('/edit/{id}', 'edit')->name('project.edit');
        Route::post('/update/{id}', 'update')->name('project.update');
        Route::get('/{project}/comments', 'getComments')->name('projects.comments');
        Route::post('/{project}/comments', 'storeComment')->name('comments.store');
        Route::post('/subprojects/{id}', 'updateSubProject')->name('subproject.update');
        Route::delete('/subprojects/{id}', 'deleteSubProject')->name('subproject.destroy');
    });

    // PROCESSES
    Route::controller(ProcessController::class)->prefix('processes')->group(function () {
        Route::get('/', 'index')->name('processList');
        Route::get('/add', 'create')->name('process.add.form');
        Route::post('/add', 'store')->name('process.add');
        Route::get('/edit/{id}', 'edit')->name('process.edit');
        Route::post('/update/{id}', 'update')->name('process.update');
    });

    // FIXED COSTS
    Route::controller(FixedCostController::class)->prefix('fixed-costs')->group(function () {
        Route::get('/', 'index')->name('fixed-costs.index');
        Route::get('/create', 'create')->name('fixed-costs.create');
        Route::post('/store/{id?}', 'store')->name('fixed-costs.store');
        Route::get('/edit/{id}', 'edit')->name('fixed-costs.edit');
    });

    // NOTIFICATIONS
    Route::controller(NotificationController::class)->prefix('notifications')->group(function () {
        Route::get('/', 'index')->name('notifications.index');
        Route::post('/mark-all-read', 'markAllRead')->name('notifications.markAllRead');
    });

});

Route::post('/woocommerce/{website}', [WoocommerceController::class, 'addWebsiteOrder']);
Route::get('/backup', [TelegramController::class, 'backUpDatabase']);

Route::post('/sms', [TelegramController::class, 'sms']);
Route::get('/sms', [TelegramController::class, 'sms']);
require __DIR__ . '/auth.php';
