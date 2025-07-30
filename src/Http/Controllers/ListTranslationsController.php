<?php

namespace LibaroIo\Languini\Http\Controllers;

use Illuminate\Support\Facades\View;

class ListTranslationsController
{
    public function __invoke(): \Illuminate\Contracts\View\View
    {
        return View::make('languini::index');
    }
}