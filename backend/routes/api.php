<?php

use App\Http\Controllers\Api\DemoAepsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| demo-provider-aeps
|--------------------------------------------------------------------------
| In production this group would sit behind auth:sanctum. Left open here so
| the demo is callable without issuing a token.
*/
Route::prefix('demo-aeps')->group(function () {
    Route::post('/transaction', [DemoAepsController::class, 'transaction']);
});
