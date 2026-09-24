<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('marketing.home');
});

Route::get('/terms', function () {
    return view('legal.show', [
        'title' => 'Terms of Service',
        'content' => \App\Models\Setting::get('terms_content', \App\Support\DefaultLegalContent::terms()),
    ]);
})->name('terms');

Route::get('/privacy', function () {
    return view('legal.show', [
        'title' => 'Privacy Policy',
        'content' => \App\Models\Setting::get('privacy_content', \App\Support\DefaultLegalContent::privacy()),
    ]);
})->name('privacy');

//ADMIN ROUTES
Route::domain(config('app.admin_domain'))->group(function () {
    Route::get('/login', \App\Livewire\Admin\LoginForm::class)->middleware('guest')->name('admin.login');

    Route::middleware(['auth', 'admin'])->group(function () {
        // No permission middleware on these two: the dashboard is every
        // staff member's landing page, and "my account" (password/email)
        // has to be reachable by anyone who can log in at all, whatever
        // else their role permits.
        Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
        Route::get('/account', \App\Livewire\Admin\Account\Edit::class)->name('admin.account.edit');

        Route::middleware('permission:manage-categories')->group(function () {
            Route::get('/categories', \App\Livewire\Admin\Categories\Index::class)->name('admin.categories.index');
            Route::get('/categories/create', \App\Livewire\Admin\Categories\Form::class)->name('admin.categories.create');
            Route::get('/categories/{category}/edit', \App\Livewire\Admin\Categories\Form::class)->name('admin.categories.edit');
        });

        Route::middleware('permission:manage-campaigns')->group(function () {
            Route::get('/campaigns', \App\Livewire\Admin\Campaigns\Index::class)->name('admin.campaigns.index');
            Route::get('/campaigns/{campaign}', \App\Livewire\Admin\Campaigns\Show::class)->name('admin.campaigns.show');
        });

        Route::get('/submissions', \App\Livewire\Admin\Submissions\Index::class)->name('admin.submissions.index')
            ->middleware('permission:verify-submissions');
        Route::get('/rejection-reasons', \App\Livewire\Admin\RejectionReasons\Index::class)->name('admin.rejection-reasons.index')
            ->middleware('permission:manage-rejection-reasons');
        Route::get('/withdrawals', \App\Livewire\Admin\Withdrawals\Index::class)->name('admin.withdrawals.index')
            ->middleware('permission:manage-withdrawals');
        Route::get('/activation-payments', \App\Livewire\Admin\ActivationPayments\Index::class)->name('admin.activation-payments.index')
            ->middleware('permission:manage-activation-payments');
        Route::get('/wallet-fundings', \App\Livewire\Admin\WalletFundings\Index::class)->name('admin.wallet-fundings.index')
            ->middleware('permission:manage-wallet-fundings');
        Route::get('/countries', \App\Livewire\Admin\Countries\Index::class)->name('admin.countries.index')
            ->middleware('permission:manage-countries');
        Route::get('/settings', \App\Livewire\Admin\Settings\Index::class)->name('admin.settings.index')
            ->middleware('permission:manage-settings');
        Route::get('/legal-pages', \App\Livewire\Admin\LegalPages\Index::class)->name('admin.legal-pages.index')
            ->middleware('permission:manage-settings');

        Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('admin.users.index')
            ->middleware('permission:manage-users');
        Route::get('/staff', \App\Livewire\Admin\Staff\Index::class)->name('admin.staff.index')
            ->middleware('permission:manage-staff');
        Route::get('/roles', \App\Livewire\Admin\Roles\Index::class)->name('admin.roles.index')
            ->middleware('permission:manage-roles');
    });
});


Route::get('/verify-email', fn () => view('verify-email'))->middleware('auth')->name('verify-email');

Route::get('/dashboard', \App\Livewire\Dashboard::class)->middleware('auth')->name('dashboard');

Route::get('/profile', \App\Livewire\Profile\Edit::class)->middleware('auth')->name('profile.edit');

Route::middleware(['auth', 'business'])->group(function () {
    Route::get('/campaigns', \App\Livewire\Campaigns\Index::class)->name('campaigns.index');
    Route::get('/wallet', \App\Livewire\Wallet\Index::class)->name('wallet.index');
    Route::get('/wallet/callback/{gateway}', [\App\Http\Controllers\WalletCallbackController::class, 'handle'])->name('wallet.callback');
    Route::get('/campaigns/create', \App\Livewire\Campaigns\Create::class)->name('campaigns.create');
    Route::get('/campaigns/{campaign}/submissions', \App\Livewire\Campaigns\Submissions::class)->name('campaigns.submissions');
    Route::get('/campaigns/{campaign}/performance', \App\Livewire\Campaigns\Performance::class)->name('campaigns.performance');
    Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index');
});

Route::middleware(['auth', 'participant'])->group(function () {
    Route::get('/tasks', \App\Livewire\Tasks\Discover::class)->name('tasks.discover');
    Route::get('/tasks/mine', \App\Livewire\Tasks\Index::class)->name('tasks.index');
    Route::get('/earnings', \App\Livewire\Earnings\Index::class)->name('earnings.index');
    Route::get('/activation/callback', [\App\Http\Controllers\ActivationCallbackController::class, 'handle'])->name('activation.callback');
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
