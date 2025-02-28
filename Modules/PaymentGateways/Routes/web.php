<?php
use App\Http\Controllers\MonnifyController;
use App\Http\Controllers\PalmpayController;

// Monnify Gateway
Route::prefix('monnify')->group(function() {
    Route::post('/initialize','MonnifyController@initializePayment')->name('monnify.initialize');
    Route::get('/callback', 'MonnifyController@handleCallback')->name('monnify.callback');
    Route::get('/success', 'MonnifyController@paymentSuccess')->name('monnify.success');
    Route::get('/failed', 'MonnifyController@paymentFailed')->name('monnify.failed');
    Route::get('/', 'MonnifyController@index')->name('monnify.test');
});

// Palmpay Gateway
Route::prefix('palmpay')->group(function () {
    Route::post('/create-order', 'PalmpayController@createOrder')->name('palmpay.createOrder');
    Route::post('/notify', 'PalmpayController@handleNotification')->name('palmpay.notify');
    Route::get('/return', 'PalmpayController@handleReturn')->name('palmpay.return');
    Route::get('/', 'PalmpayController@index')->name('palmpay.test');
    Route::post('/query-order', 'PalmpayController@queryOrder')->name('palmpay.queryOrder');
    Route::post('/create-wallet-order', 'PalmpayController@createWalletOrder')->name('palmpay.createWalletOrder');
    Route::post('/query-wallet-order', 'PalmpayController@queryWalletOrder')->name('palmpay.queryWalletOrder');
    Route::post('/create-bank-transfer-order', 'PalmpayController@createBankTransferOrder')->name('palmpay.createBankTransferOrder');
    Route::post('/query-bank-transfer-order', 'PalmpayController@cqueryBankTransferOrder')->name('palmpay.queryBankTransferOrder');
});