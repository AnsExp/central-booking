<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TrainController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');

Route::get('/dashboard/trains', [TrainController::class, 'view'])->name('trains.view');
Route::get('/dashboard/routes', [RouteController::class, 'view'])->name('routes.view');
Route::get('/dashboard/stations', [StationController::class, 'view'])->name('stations.view');
Route::get('/dashboard/services', [ServiceController::class, 'view'])->name('services.view');
Route::get('/dashboard/trains/form', [TrainController::class, 'form'])->name(   'trains.form');
Route::get('/dashboard/routes/form', [RouteController::class, 'form'])->name('routes.form');
Route::get('/dashboard/stations/form', [StationController::class, 'form'])->name('stations.form');
Route::get('/dashboard/services/form', [ServiceController::class, 'form'])->name('services.form');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/trains', [TrainController::class, 'store'])->name('trains.store');
Route::post('/routes', [RouteController::class, 'store'])->name('routes.store');
Route::post('/stations', [StationController::class, 'store'])->name('stations.store');
Route::post('/services', [ServiceController::class, 'store'])->name('services.store');

Route::put('/trains/{train}', [TrainController::class, 'update'])->name('trains.update');
Route::put('/routes/{route}', [RouteController::class, 'update'])->name('routes.update');
Route::put('/stations/{station}', [StationController::class, 'update'])->name('stations.update');
Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');

Route::delete('/trains/{train}', [TrainController::class, 'destroy'])->name('trains.destroy');
Route::delete('/routes/{route}', [RouteController::class, 'destroy'])->name('routes.destroy');
Route::delete('/stations/{station}', [StationController::class, 'destroy'])->name('stations.destroy');
Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/403', [PageController::class, 'forbidden'])->name('forbidden');
Route::fallback([PageController::class, 'notFound']);
