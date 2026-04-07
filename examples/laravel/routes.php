<?php

use App\Http\Controllers\CallbackController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::post('/checkout/ixopay/debit', [CheckoutController::class, 'debit'])->name('ixopay.debit');
Route::post('/checkout/ixopay/callback', CallbackController::class)->name('ixopay.callback');

Route::view('/checkout/ixopay/success', 'ixopay.success')->name('ixopay.success');
Route::view('/checkout/ixopay/cancel', 'ixopay.cancel')->name('ixopay.cancel');
Route::view('/checkout/ixopay/pending', 'ixopay.pending')->name('ixopay.pending');
