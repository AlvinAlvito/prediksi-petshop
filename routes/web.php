<?php

use App\Http\Controllers\Api\ChartController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\KoefisieniController;
use App\Http\Controllers\PrediksiController;

// ===================
// Halaman Login
// ===================
Route::get('/', function () {
    return view('login');
})->name('login');

// ===================
// Proses Login Manual
// ===================
Route::post('/', function (Request $request) {
    $username = $request->username;
    $password = $request->password;

    if ($username === 'admin' && $password === '123') {
        session(['is_admin' => true]);
        return redirect('/admin');
    }

    return back()->withErrors(['login' => 'Username atau Password salah!']);
})->name('login.proses');

// ===================
// Logout
// ===================
Route::get('/logout', function () {
    session()->forget('is_admin');
    return redirect('/');
})->name('logout');

// ===================
// Dashboard Admin
// ===================
use App\Http\Controllers\DashboardController;


Route::get('/admin', function () {
    if (!session('is_admin')) {
        return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
    }
    return view('admin.index');
})->name('admin.index');



// ===================
// CRUD Data Penjualan
// ===================

Route::get('/admin/data-penjualan', function () {
    if (!session('is_admin')) return redirect('/');
    // index() sudah mengembalikan view dengan variabel $penjualans
    return app(PenjualanController::class)->index(request());
})->name('penjualan.index');

Route::post('/admin/data-penjualan', function (Request $request) {
    if (!session('is_admin')) return redirect('/');
    return app(PenjualanController::class)->store($request);
})->name('penjualan.store');

Route::delete('/admin/data-penjualan/{id}', function ($id) {
    if (!session('is_admin')) return redirect('/');
    return app(PenjualanController::class)->destroy($id);
})->name('penjualan.destroy');

Route::put('/admin/data-penjualan/{id}', function (Request $request, $id) {
    if (!session('is_admin')) return redirect('/');
    return app(PenjualanController::class)->update($request, $id);
})->name('penjualan.update');


// FTS SEMESTA
Route::get('/admin/fts/semesta', function (Request $request) {
    if (!session('is_admin')) return redirect('/');
    return app(PenjualanController::class)->semestaU($request);
})->name('fts.semesta');




