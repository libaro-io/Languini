<?php

namespace LibaroIo\Languini\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use LibaroIo\Languini\Services\LanguagesService;

class ListTranslationsController
{
    public function __invoke(Request $request): \Illuminate\Contracts\View\View
    {
        return View::make('languini::index', [
            'fileNames' => LanguagesService::getFilesForTranslations(),
            'translatableLanguages' => LanguagesService::getLanguages(),
            'translationKeys' => LanguagesService::getTranslationComparisonForFile($request->get('filename')),
        ]);
    }
}