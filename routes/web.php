<?php

use App\Http\Controllers\AkunController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DataMaterialController;
use App\Http\Controllers\MaterialMasukController;
use App\Http\Controllers\MaterialKeluarController;
use App\Http\Controllers\StokMaterialController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SupplierController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::group(['prefix' => 'dashboard/admin'], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [HomeController::class, 'profile'])->name('profile');
        Route::post('update', [HomeController::class, 'updateprofile'])->name('profile.update');
    });



    Route::controller(AkunController::class)
        ->prefix('akun')
        ->as('akun.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('showdata', 'dataTable')->name('dataTable');
            Route::match(['get', 'post'], 'tambah', 'tambahAkun')->name('add');
            Route::match(['get', 'post'], '{id}/ubah', 'ubahAkun')->name('edit');
            Route::delete('{id}/hapus', 'hapusAkun')->name('delete');
        });

    Route::controller(DataMaterialController::class)
        ->prefix('dataMaterial')
        ->as('dataMaterial.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('showdata', 'dataTable')->name('dataTable');
            Route::match(['get', 'post'], 'tambah', 'tambahMaterial')->name('add');
            Route::match(['get', 'post'], '{id}/ubah', 'ubahMaterial')->name('edit');
            Route::delete('{id}/hapus', 'hapusMaterial')->name('delete');
            Route::get('/pdf', 'downloadPdf')->name('downloadPdf');
        });

    Route::controller(MaterialMasukController::class)
        ->prefix('materialMasuk')
        ->as('materialMasuk.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('showdata', 'dataTable')->name('dataTable');
            Route::match(['get', 'post'], 'tambah', 'tambahMaterialMasuk')->name('add');
            Route::match(['get', 'post'], '{id}/ubah', 'ubahMaterialMasuk')->name('edit');
            Route::delete('{id}/hapus', 'hapusMaterialMasuk')->name('delete');
            Route::get('/pdf', 'downloadPdf')->name('downloadPdf');
        });

    Route::prefix('materialKeluar')
        ->as('materialKeluar.')
        ->group(function () {
            Route::get('/', [MaterialKeluarController::class, 'index'])->name('index');
            Route::post('showdata', [MaterialKeluarController::class, 'dataTable'])->name('dataTable');
            Route::match(['get', 'post'], 'tambah', [MaterialKeluarController::class, 'tambahMaterialKeluar'])->name('add');
            Route::match(['get', 'post'], '{id}/ubah', [MaterialKeluarController::class, 'ubahMaterialKeluar'])->name('edit');
            Route::delete('{id}/hapus', [MaterialKeluarController::class, 'hapusMaterialKeluar'])->name('delete');
            Route::get('/pdf', [MaterialKeluarController::class, 'downloadPdf'])->name('downloadPdf');
        });

    Route::controller(StokMaterialController::class)
        ->prefix('stokMaterial')
        ->as('stokMaterial.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('showdata', 'dataTable')->name('dataTable');
            Route::match(['get', 'post'], 'tambah', 'tambahStokMaterial')->name('add');
            Route::match(['get', 'post'], 'ubah', 'ubahStokMaterial')->name('edit');
            Route::delete('{id}/hapus', 'hapusStokMaterial')->name('delete');
            Route::get('/pdf', 'downloadPdf')->name('downloadPdf');
        });

    Route::controller(SupplierController::class)
        ->prefix('supplier')
        ->as('supplier.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('showdata', 'dataTable')->name('dataTable');
            Route::match(['get', 'post'], 'add', 'add')->name('add');
            Route::match(['get', 'post'], '{id}/ubah', 'edit')->name('edit');
            Route::delete('{id}/hapus', 'delete')->name('delete');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

    Route::controller(CustomerController::class)
        ->prefix('customer')
        ->as('customer.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('showdata', 'dataTable')->name('dataTable');
            Route::match(['get', 'post'], 'add', 'add')->name('add');
            Route::match(['get', 'post'], '{id}/ubah', 'edit')->name('edit');
            Route::delete('{id}/hapus', 'delete')->name('delete');
            Route::get('/pdf', 'pdf')->name('pdf');
        });
});
