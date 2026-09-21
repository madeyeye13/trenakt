<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('marketing.home');
});

Route::view('/terms', 'legal.placeholder', ['title' => 'Terms of Service'])->name('terms');
Route::view('/privacy', 'legal.placeholder', ['title' => 'Privacy Policy'])->name('privacy');

//ADMIN ROUTES
Route::domain('admin.trenakt.test')->group(function () {
    Route::get('/login', \App\Livewire\Admin\LoginForm::class)->middleware('guest')->name('admin.login');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
        Route::get('/categories', \App\Livewire\Admin\Categories\Index::class)->name('admin.categories.index');
        Route::get('/categories/create', \App\Livewire\Admin\Categories\Form::class)->name('admin.categories.create');
        Route::get('/categories/{category}/edit', \App\Livewire\Admin\Categories\Form::class)->name('admin.categories.edit');
    });
});


Route::get('/verify-email', fn () => view('verify-email'))->middleware('auth')->name('verify-email');

Route::get('/dashboard', \App\Livewire\Dashboard::class)->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'business'])->group(function () {
    Route::get('/campaigns', \App\Livewire\Campaigns\Index::class)->name('campaigns.index');
    Route::get('/wallet', \App\Livewire\Wallet\Index::class)->name('wallet.index');
    Route::get('/wallet/callback/{gateway}', [\App\Http\Controllers\WalletCallbackController::class, 'handle'])->name('wallet.callback');
    Route::get('/campaigns/create', \App\Livewire\Campaigns\Create::class)->name('campaigns.create');
    Route::get('/campaigns/{campaign}/submissions', \App\Livewire\Campaigns\Submissions::class)->name('campaigns.submissions');
});

Route::post('/webhooks/paystack', [\App\Http\Controllers\Webhooks\PaystackWebhookController::class, 'handle'])->name('webhooks.paystack');
Route::post('/webhooks/flutterwave', [\App\Http\Controllers\Webhooks\FlutterwaveWebhookController::class, 'handle'])->name('webhooks.flutterwave');
Route::get('/login', fn () => view('auth.login'))->middleware('guest')->name('login');
Route::get('/register', fn () => view('auth.register'))->middleware('guest')->name('register');

Route::get('/reset-password/{token}', fn (Request $request) => view('auth.reset-password', ['request' => $request]))
    ->middleware('guest')
    ->name('password.reset');

    Route::get('/forgot-password', fn () => view('auth.forgot-password'))
    ->middleware('guest')
    ->name('password.request');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');