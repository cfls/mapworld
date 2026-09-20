<?php

use App\Models\Country;
use App\Models\MarineArea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Admin auth
Route::get('/admin/login', fn () => view('admin.login'))
    ->middleware('guest')
    ->name('login');

Route::post('/admin/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('admin.logout');

// Admin protected
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/countries')->name('dashboard');
    Route::view('/countries', 'admin.countries')->name('countries');
    Route::get('/countries/{country}/videos', function (Country $country) {
        return view('admin.videos', compact('country'));
    })->name('videos');
    Route::view('/marine-areas', 'admin.marine-areas-list')->name('marine-areas');
    Route::get('/marine-areas/{area}/videos', function (MarineArea $area) {
        return view('admin.marine-areas', compact('area'));
    })->name('marine-area-videos');
});
