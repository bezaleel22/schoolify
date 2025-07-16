<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Result\Http\Controllers\Api\GenerateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('remark-filter', 'ResultController@filter')->name('result.remark.filter');
Route::get('result/generate', [GenerateController::class, 'index']);
Route::get('openrouter/limits', 'MarkRegisterController@checkApiLimits')->name('result.openrouter.limits');
Route::middleware('auth:api')->get('/result', function (Request $request) {
    return $request->user();
});