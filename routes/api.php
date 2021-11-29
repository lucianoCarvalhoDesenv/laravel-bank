<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtigoController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;



Route::group([
    'middleware' => 'api',

], function ($router) {
    Route::get('transactionbyid', [TransactionController::class, 'getbyid']);
    Route::get('transaction', [TransactionController::class, 'index']);
    Route::get('expenses', [TransactionController::class, 'expenses']);
    Route::get('incomes', [TransactionController::class, 'incomes']);
    Route::get('mychecks', [TransactionController::class, 'mychecks']);
    Route::get('pendingapproval', [TransactionController::class, 'waitingtransactions']);
    Route::get('balance', [TransactionController::class, 'balance']);
    
 
    Route::post('deposit', [TransactionController::class, 'submit_check']);
    Route::post('approve', [TransactionController::class, 'approveCheck']);
    Route::post('payment', [TransactionController::class, 'payment']);
    //teste
    Route::post('transaction', [TransactionController::class, 'test_force_transaction']);
    Route::post('hotdeposit', [TransactionController::class, 'auto_approved_deposit']);

});


Route::group([
    'middleware' => 'api',
   //'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);    
    Route::get('/me', [AuthController::class, 'me']);
});


Route::group([
    'middleware' => 'api',
    //'prefix' => 'wallet'
], function ($router) {
Route::get('wallet', [WalletController::class, 'index']); // List 
Route::get('wallet/{id}', [WalletController::class, 'show']); // List single 
Route::post('wallet', [WalletController::class, 'store']); // Create new 
Route::put('wallet/{id}', [WalletController::class, 'update']); // Update 
Route::delete('wallet/{id}', [WalletController::class,'destroy']); // Delete 
});

Route::group([
    'middleware' => 'api',
    //'prefix' => 'wallet'
], function ($router) {
Route::get('user', [UserController::class, 'index']); // List 
Route::get('user/{id}', [UserController::class, 'show']); // List single 
Route::post('user', [UserController::class, 'store']); // Create new 
Route::put('user/{id}', [UserController::class, 'update']); // Update 
Route::delete('user/{id}', [UserController::class,'destroy']); // Delete 
});

