<?php

namespace LibaroIo\Languini\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class UpdateTranslationsController
{
    public function __invoke(Request $request): \Illuminate\Contracts\View\View
    {
        dd($request->all());
    }
}