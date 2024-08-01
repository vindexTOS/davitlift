<?php
use App\Models\Device;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankOfGeorgia;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MqttController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CardController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DeviceController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Middleware\ComapnyAccsessMiddleware;
  use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Controllers\UpdatingDeviceController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\UnregisteredDeviceController;
use App\Http\Middleware\ComapnyAndManagerAccsessMiddleware;

//  ADMIN ONLY

Route::middleware(['auth:api', 'SuperAdminMiddleware'])->group(function () {
    // FILES
    
    Route::get('/files', [FileController::class, 'index']);
    Route::delete('/files/{id}', [FileController::class, 'delete']);
    
    Route::get('/files/{name}/{version}', [
        FileController::class,
        'deviceUpdate',
    ]);
    
    Route::post('/send/update/to/selected/devices', [
        FileController::class,
        'deviceUpdateByArray',
    ]);
    Route::post('/upload', [FileController::class, 'upload']);
    Route::get('/unregistered_device', [
        UnregisteredDeviceController::class,
        'get',
    ]);
    Route::delete('/unregistered_device', [
        UnregisteredDeviceController::class,
        'delete',
    ]);
    
    // DEVICES
    Route::get('/updating-device/last-created', [
        UpdatingDeviceController::class,
        'getLastCreated',
    ]);
    
    Route::get('/updating-device/check-failed/last-created', [
        UpdatingDeviceController::class,
        'checkFailed',
    ]);
    //  update deveice tarriff aomunt
    Route::put('/device/tariff/{id}', [
        DeviceController::class,
        'updateDeviceTariff',
    ]);
    // update many  traffic amounts
    Route::put('/device/manyTariff/{managerId}', [
        DeviceController::class,
        'updateManyDeviceTariff',
    ]);
});
//  company middleware

Route::middleware(['auth:api', 'ComapnyAccsessMiddleware'])->group(function () {
    Route::put('/updateUser', [UserController::class, 'update']);
    Route::apiResource('users', UserController::class);
    
    Route::get('/change/user/password/admin/{user_id}/{password}', [
        UserController::class,
        'changePassword',
        // sssds
    ]);
    
    Route::put('/update/user/subscription', [
        UserController::class,
        'updateUserSubscription',
    ]);
    
    Route::put("/update-fixed-card-amount", [UserController::class, "UpdateUsersFixedCardTarriff"]);
    
});

//  company and manager middle ware

Route::middleware(['auth:api', 'ComapnyAndManagerAccsessMiddleware'])->group(
    function () {
        // delete  user
        Route::delete('/userRemoveDevice/{user_id}/{device_id}', [
            UserController::class,
            'removeToDevice',
        ]);
        // add user to device
        Route::get('/userToDevice/{user_search}/{device_id}', [
            UserController::class,
            'addToDevice',
        ]);
    }
);

//  tbc fast pay

// this works for both liberty and tbc
Route::post('/transaction/checkuser', [
    TransactionController::class,
    'checkIfUserExists',
]);

Route::post('/transaction/tbcfastpay', [
    TransactionController::class,
    'makeTbcFastPayOrder',
]);
//  liberty bank
Route::post('/transaction/lbfastpay', [
    TransactionController::class,
    'makeLbrtFastPayOrder',
]);

// USER ONLY OR SHARED

Route::middleware(['auth:api'])->group(function () {
    // COMPANIES
    Route::apiResource('companies', CompanyController::class);
    Route::get('/companies/manager/{id}', [
        CompanyController::class,
        'manager',
    ]);
    Route::get('/companies/block/{id}', [
        CompanyController::class,
        'blockCompanies',
    ]);
    Route::get('/companies/unblock/{id}', [
        CompanyController::class,
        'unblockCompanies',
    ]);
    Route::get('/companies/block/manager/{id}', [
        CompanyController::class,
        'blockManager',
    ]);
    Route::get('/companies/unblock/manager/{id}', [
        CompanyController::class,
        'unblockManager',
    ]);
    Route::get('/companies/unhideStatistic/manager/{id}', [
        CompanyController::class,
        'unhideStatistic',
    ]);
    Route::get('/companies/hideStatistic/manager/{id}', [
        CompanyController::class,
        'hideStatistic',
    ]);
    
    // CARDS
    Route::apiResource('cards', CardController::class);
    Route::get('/user/cards/{id}', [CardController::class, 'getUserCards']);
    Route::post('/user/add/card', [CardController::class, 'storeForUser']);
    Route::get('/cards/generate/code', [
        CardController::class,
        'generateElevatorCode',
    ]);
    Route::get('/cards/getLift/calltolift', [
        CardController::class,
        'calltolift',
    ]);
    // TRANSACTIONS
    Route::apiResource('transactions', TransactionController::class);
    Route::get('transactions/generate/order', [
        TransactionController::class,
        'makeOrderTransaction',
    ]);
    // ხელით შეცვლა ბალანსის
    Route::put('/transaction/update-balance', [
        TransactionController::class,
        'updateBalanceByHand',
    ]);
    Route::get('/changeManager/{company_id}/{user_id}/{new_email}', [
        UserController::class,
        'changeManager',
    ]);
    //  USERS
    Route::get('user', [AuthController::class, 'user']);
    
    Route::post('/password/change', [
        UserController::class,
        'changeUserPassword',
    ]);
    
    // FILES
    
    Route::get('/get/pay/cashback/{company_id}/{manager_id}/', [
        CompanyController::class,
        'payedCashback',
    ]);
    Route::get('/get/pay/companycashback/{company_id}', [
        CompanyController::class,
        'payedCashbackForCompany',
    ]);
    Route::post('/pay/cashback', [CompanyController::class, 'payCashback']);
    Route::delete('/cashback/{id}', [
        CompanyController::class,
        'deleteCashback',
    ]);
    Route::get('/cashback/{user_id}/{cashback}', [
        UserController::class,
        'cashback',
    ]);
    
    Route::get('/balance/user', [UserController::class, 'getBalance']);
    Route::get('/bank/transaction/detail/{order_id}', [
        TransactionController::class,
        'getTransactionDetail',
    ]);
    Route::get('/bank/transaction/create/{amount}/{userId}', [
        TransactionController::class,
        'createTransaction',
    ]);
    Route::get('/per/user/transactions/{id}', [
        TransactionController::class,
        'perUserTransaction',
    ]);
    // DEVICES
    
    Route::apiResource('devices', DeviceController::class);
    Route::put('/deviceEarn/edit', [DeviceController::class, 'EditDevicEarn']);
    Route::get('/get/devices/user', [DeviceController::class, 'userDevice']);
    Route::get('/get/devices/user/{id}', [
        DeviceController::class,
        'userDeviceUser',
    ]);
    Route::put('/devices/{device}/reset', [
        DeviceController::class,
        'resetDevice',
    ]);
    Route::put('/devices/{device}/appconf', [
        DeviceController::class,
        'setAppConf',
    ]);
    Route::put('/devices/{device}/extconf', [
        DeviceController::class,
        'setExtConf',
    ]);
    Route::delete('/device/error/{id}', [
        DeviceController::class,
        'deleteError',
    ]);
    Route::get('/updating-device/check-success/last-created', [
        UpdatingDeviceController::class,
        'checkSuccess',
    ]);
});
// END OF AUTH MIDDLEWEAR
Route::get('/bank/transaction/detail/{order_id}', [
    TransactionController::class,
    'getTransactionDetail',
]);

Route::post('/bank/transaction/callback', [
    TransactionController::class,
    'transactionCallback',
]);
Route::get('/test', [TransactionController::class, 'updateUserData']);

Route::post('logout', function () {
    return Auth::id();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('/download/{file}', [FileController::class, 'download']);
Route::get('mqtt/general', [MqttController::class, 'handleGeneralEvent']);
Route::get('mqtt/heartbeat', [MqttController::class, 'handleHeartbeatEvent']);
Route::post('/uploadForHttp', [FileController::class, 'uploadForHttp']);
Route::delete('/filesForFileServer/{id}', [
    FileController::class,
    'deleteForFileServer',
]);

Route::get('user/transaction/history/{device_id}', [
    UserController::class,
    'UserTransactionsBasedOnDevice',
]);


//  testing area 

Route::get("/ipay/verification/", [BankOfGeorgia::class, 'VerifyUser']);
Route::get("/ipay/payment/", [BankOfGeorgia::class, "handlePayment"]);

Route::get("/elevatoruse/{user_id}", [UserController::class, "GetUsersElevatorUse"]);
Route::post("/testing-fix", [TestController::class, 'TestFixedCard']);
Route::get("/test-time-zone", [TestController::class, "TestTimeZone"]);