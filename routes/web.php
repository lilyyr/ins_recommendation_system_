<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\JobApplicationController;

Route::get('/', function () {
    return view('home');
})->name('home');
Route::get('/job-application', [JobApplicationController::class, 'index'])->name('job-application.index');
Route::post('/job-application/store', [JobApplicationController::class, 'store'])->name('job-application.store');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Agent management
    Route::get('/agents', [AdminController::class, 'agentsIndex'])->name('agents.index');
    Route::get('/agents/create', [AdminController::class, 'agentsCreate'])->name('agents.create');
    Route::post('/agents', [AdminController::class, 'agentsStore'])->name('agents.store');
    Route::get('/agents/{id}/edit', [AdminController::class, 'agentsEdit'])->name('agents.edit');
    Route::put('/agents/{id}', [AdminController::class, 'agentsUpdate'])->name('agents.update');
    Route::delete('/agents/{id}', [AdminController::class, 'agentsDestroy'])->name('agents.destroy');

    // Product management
    Route::get('/products', [AdminController::class, 'productsIndex'])->name('products.index');
    Route::get('/products/create', [AdminController::class, 'productsCreate'])->name('products.create');
    Route::post('/products', [AdminController::class, 'productsStore'])->name('products.store');
    Route::get('/products/{id}/edit', [AdminController::class, 'productsEdit'])->name('products.edit');
    Route::put('/products/{id}', [AdminController::class, 'productsUpdate'])->name('products.update');
    Route::delete('/products/{id}', [AdminController::class, 'productsDestroy'])->name('products.destroy');

    // Weight management
    Route::get('/weights', [AdminController::class, 'weightsIndex'])->name('weights.index');
    Route::post('/weights', [AdminController::class, 'weightsUpdate'])->name('weights.update');

    // Model training
    Route::post('/train-model', [AdminController::class, 'trainModel'])->name('train-model');
    Route::get('/train-model/status/{id}', [AdminController::class, 'trainModelStatus'])->name('train-model.status');

    // Metric visualization
    Route::get('/metrics/index', [AdminController::class, 'metrics'])->name('metrics.index');

    // Job application management
    Route::get('/job-application/admin', [JobApplicationController::class, 'adminIndex'])->name('job-application.admin.index');
    Route::get('/job-application/admin/{id}', [JobApplicationController::class, 'adminShow'])->name('job-application.admin.show');
    Route::put('/job-application/admin/{id}', [JobApplicationController::class, 'adminUpdate'])->name('job-application.admin.update');
    Route::delete('/job-application/admin/{id}', [JobApplicationController::class, 'adminDelete'])->name('job-application.admin.delete');
    Route::get('/job-application/{id}/download-resume', [JobApplicationController::class, 'downloadResume'])->name('job-application.resume.download');
});

// Agent & Admin routes
Route::middleware(['auth', 'role:admin,agent'])->group(function () {
    Route::controller(ConsultationController::class)->prefix('consultations')->name('consultations.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/process', 'process')->name('process');
    });

    // Client management
    Route::resource('clients', ClientController::class);
});

// Client routes
Route::middleware(['auth', 'role:client'])->prefix('client')->group(function () {
    Route::get('/consultations', [ClientController::class, 'myConsultations'])->name('client.consultations');
    Route::get('/consultations/{id}', [ClientController::class, 'showConsultation'])->name('client.consultations.show');
});
