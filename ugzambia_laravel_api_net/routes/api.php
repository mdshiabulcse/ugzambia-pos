<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('data-import/{periodName}',[\App\Http\Controllers\DataImportApiController::class,'index']);
Route::get('data-import-period',[\App\Http\Controllers\DataImportApiController::class,'importPeriod']);
Route::get('data-import-latest-period',[\App\Http\Controllers\DataImportApiController::class,'importLatestPeriod']);
Route::get('all-data-get',[\App\Http\Controllers\DataImportApiController::class,'allDataGetForNet']);
Route::get('test-duplicate-data',[\App\Http\Controllers\DataImportApiController::class,'duplicateData']);
Route::get('allocation-missing-user-search',[\App\Http\Controllers\DataImportApiController::class,'allocationMissingUserSearch']);

