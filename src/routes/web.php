<?php

use Illuminate\Support\Facades\Route;
use LibaroIo\Languini\Http\Controllers\ListTranslationsController;

Route::get('/languini', ListTranslationsController::class);
