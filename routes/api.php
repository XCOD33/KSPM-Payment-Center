<?php

use App\Http\Controllers\Api\PembayarankuController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// }); 

Route::group(['middleware', 'auth:sanctum'], function () {
  route::group(['prefix' => 'users', 'controller' => UserController::class], function () {
    route::post('login', 'login');
    route::get('detail', 'detail');
    Route::post('change-password', 'change_password');
    route::post('logout', 'logout');
  });
  route::group(['prefix' => 'pembayaranku', 'controller' => PembayarankuController::class], function () {
    route::get('/simple', 'simple');
    route::get('/bills', 'bills');
    route::get('/bills/{url}', 'bill_detail');
    route::post('/bills/{url}/pay', 'pay');
  });
});

route::post('/tripay/callback', [PembayarankuController::class, 'tripay_callback']);
