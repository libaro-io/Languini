<?php

use Illuminate\Support\Facades\Route;
use LibaroIo\Languini\Http\Controllers\ListTranslationsController;
use LibaroIo\Languini\Http\Controllers\UpdateTranslationsController;

Route::get('/languini', ListTranslationsController::class)
    ->name('languini.index');
Route::post('/languini', UpdateTranslationsController::class)
    ->name('languini.update');
