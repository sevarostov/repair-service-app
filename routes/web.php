<?php

use App\Http\Controllers\RequestController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth', EnsureUserHasRole::class . ':dispatcher|master'])->group(function () {
	Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
	Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
	Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
	Route::post('/requests/{id}/assign', [RequestController::class, 'assign'])->name('requests.assign');
	Route::patch('/requests/{id}/cancel', [RequestController::class, 'cancel'])->name('requests.cancel');
	Route::patch('/requests/{id}/take', [RequestController::class, 'take'])->name('requests.take');
	Route::patch('/requests/{id}/done', [RequestController::class, 'done'])->name('requests.done');
});
