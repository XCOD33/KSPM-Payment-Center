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
        Route::group(['prefix' => 'manage', 'as' => 'manage.', 'middleware' => 'role:super-admin'], function () {
            Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
                Route::get('/', [App\Http\Controllers\DashboardController::class, 'manage_users_get'])->name('index');
                Route::post('/detail', [App\Http\Controllers\DashboardController::class, 'detail_user'])->name('detail');
                Route::post('/create', [App\Http\Controllers\DashboardController::class, 'create_user'])->name('create');
                Route::post('/update', [App\Http\Controllers\DashboardController::class, 'update_user'])->name('update');
                Route::post('/delete', [App\Http\Controllers\DashboardController::class, 'delete_user'])->name('delete');

                Route::get('/download-excel', [App\Http\Controllers\DashboardController::class, 'download_excel'])->name('download_excel');
                Route::post('/upload-excel', [App\Http\Controllers\DashboardController::class, 'upload_excel'])->name('upload_excel');
            });
            Route::group(['prefix' => 'roles', 'as' => 'roles.'], function () {
                Route::get('/', [App\Http\Controllers\Dashboards\RolesController::class, 'manage_roles_get'])->name('index');
                Route::get('/get-roles', [App\Http\Controllers\Dashboards\RolesController::class, 'get_roles'])->name('get_roles');
                Route::post('/create', [App\Http\Controllers\Dashboards\RolesController::class, 'create'])->name('create');
                Route::post('/view', [App\Http\Controllers\Dashboards\RolesController::class, 'view'])->name('view');
                Route::post('/view-roles', [App\Http\Controllers\Dashboards\RolesController::class, 'view_roles'])->name('view_roles');
                Route::post('/edit', [App\Http\Controllers\Dashboards\RolesController::class, 'edit'])->name('edit');
                Route::post('/update', [App\Http\Controllers\Dashboards\RolesController::class, 'update'])->name('update');
                Route::post('/delete', [App\Http\Controllers\Dashboards\RolesController::class, 'delete'])->name('delete');
                Route::post('/add-user', [App\Http\Controllers\Dashboards\RolesController::class, 'add_user'])->name('add_user');
                Route::post('/remove-user', [App\Http\Controllers\Dashboards\RolesController::class, 'remove_user'])->name('remove_user');
            });
            Route::group(['prefix' => 'permission', 'as' => 'permission.'], function () {
                Route::get('/', [App\Http\Controllers\Dashboards\PermissionController::class, 'index'])->name('index');
            });
            Route::get('/get-users', [App\Http\Controllers\DashboardController::class, 'get_users'])->name('get_users');
        });
        Route::group(['prefix' => 'pembayaran', 'as' => 'pembayaran.'], function () {
            Route::get('/', [App\Http\Controllers\Dashboards\PembayaranController::class, 'index'])->name('index');
            Route::get('/get-pembayaran', [App\Http\Controllers\Dashboards\PembayaranController::class, 'get_pembayaran'])->name('get_pembayaran');
            Route::post('/get-pembayaran-user', [App\Http\Controllers\Dashboards\PembayaranController::class, 'get_pembayaran_user'])->name('get_pembayaran_user');
            Route::post('/detail', [App\Http\Controllers\Dashboards\PembayaranController::class, 'detail'])->name('detail');
            Route::post('/store', [App\Http\Controllers\Dashboards\PembayaranController::class, 'store'])->name('store');
            Route::post('/view', [App\Http\Controllers\Dashboards\PembayaranController::class, 'view'])->name('view');
            Route::get('/channel', [App\Http\Controllers\Dashboards\PembayaranController::class, 'channel'])->name('channel');
            Route::get('/{id}', [App\Http\Controllers\Dashboards\PembayaranController::class, 'bayar'])->name('bayar');
            Route::post('/bayar', [App\Http\Controllers\Dashboards\PembayaranController::class, 'bayar_post'])->name('bayar_post');
            Route::post('/edit', [App\Http\Controllers\Dashboards\PembayaranController::class, 'edit'])->name('edit');
            Route::post('/update', [App\Http\Controllers\Dashboards\PembayaranController::class, 'update'])->name('update');
            Route::post('/delete', [App\Http\Controllers\Dashboards\PembayaranController::class, 'delete'])->name('delete');
        });
        Route::group(['prefix' => 'pembayaranku', 'as' => 'pembayaranku.'], function () {
            Route::get('/', [App\Http\Controllers\Dashboards\PembayarankuController::class, 'index'])->name('index');
            Route::get('/pembayarans', [App\Http\Controllers\Dashboards\PembayarankuController::class, 'pembayarans'])->name('pembayarans');
            Route::post('/invoice', [App\Http\Controllers\Dashboards\PembayarankuController::class, 'invoice'])->name('invoice');
        });
    });
});
Route::post('/callback', [App\Http\Controllers\Dashboards\PembayaranController::class, 'callback'])->middleware('guest')->name('callback');
