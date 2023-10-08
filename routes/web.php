<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [App\Http\Controllers\AuthController::class, 'login_get'])->name('login');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login_post'])->name('login_post');
});

Route::group(['middleware' => 'isLogin'], function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        Route::group(['prefix' => 'manage', 'as' => 'manage.'], function () {
            Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
                Route::get('/', [App\Http\Controllers\DashboardController::class, 'manage_users_get'])->name('index');
                Route::post('/create', [App\Http\Controllers\DashboardController::class, 'create_user'])->name('create');
                Route::post('/delete', [App\Http\Controllers\DashboardController::class, 'delete_user'])->name('delete');
            });
            Route::get('/get-users', [App\Http\Controllers\DashboardController::class, 'get_users'])->name('get_users');
        });
    });
});
