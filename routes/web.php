<?php

use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/performance-connections', [SiteController::class, 'performance'])->name('performance');
Route::get('/brand-consulting', [SiteController::class, 'brand'])->name('brand');
Route::get('/social-media-strategies', [SiteController::class, 'social'])->name('social');
Route::get('/experience-tech', [SiteController::class, 'experience'])->name('experience');
Route::get('/makitha', [SiteController::class, 'makitha'])->name('makitha');
Route::get('/casos', [SiteController::class, 'cases'])->name('cases');
Route::get('/nosotros', [SiteController::class, 'about'])->name('about');
Route::get('/contacto', [SiteController::class, 'contact'])->name('contact');
Route::post('/contacto', [ContactSubmissionController::class, 'store'])->middleware('throttle:6,1')->name('contact.store');
