<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;
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

Route::get('/login', function () {
    return redirect('/');
});

// default middlewares - guest, auth
//  gust middleware redirect to the dashboard if authenticated/logined - dashboard route given in route
// service provider  - call from middleware
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'loadRegister']);
    Route::post('/register', [AuthController::class, 'userRegister'])->name('userRegister');

    Route::get('/', [AuthController::class, 'loadLogin']);
    Route::post('/', [AuthController::class, 'userLogin'])->name('userLogin');
});

Route::middleware('auth')->group(function () {

    // default auth middleware redirect to / route means here login page if not authenticated.
    Route::middleware('userAuth')->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    });

    // Route::middleware(['auth', 'isAuthenticate'])->group(function () {
    Route::get('/subscription', [SubscriptionController::class, 'loadSubscription'])->name('subscription');
    Route::post('/get-plan-details', [SubscriptionController::class, 'getPlanDetails'])->name('getPlanDetails');

    // logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // create subscription
    Route::post('/create-subscription', [SubscriptionController::class, 'createSubscription'])->name('createSubscription');
    // cancel subscription
    Route::post('/cancel-subscription', [SubscriptionController::class, 'cancelSubscription'])->name('cancelSubscription');
    // });
});

// stripe webhook
Route::post('webhook-subscription', [SubscriptionController::class, 'webhookSubscription'])->name('webhookSubscription');


