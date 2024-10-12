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

Route::get('result/generate', [GenerateController::class, 'index']);
Route::middleware('auth:api')->get('/result', function (Request $request) {
    return $request->user();
});