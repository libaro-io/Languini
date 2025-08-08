<?php

use Illuminate\Support\Facades\Route;
use LibaroIo\Languini\Http\Controllers\ListTranslationsController;
use LibaroIo\Languini\Http\Controllers\TranslateKeysController;
use LibaroIo\Languini\Http\Controllers\UpdateTranslationsController;

Route::get('/languini', ListTranslationsController::class)
    ->name('languini.index');
Route::post('/languini', UpdateTranslationsController::class)
    ->name('languini.update');
Route::post('/languini/ai-translate', TranslateKeysController::class)
    ->name('languini.ai-translate');
