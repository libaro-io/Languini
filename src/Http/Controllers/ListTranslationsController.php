<?php

namespace LibaroIo\Languini\Http\Controllers;


use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class ListTranslationsController
{
    public function __invoke(): \Illuminate\Contracts\View\View
    {
        $langDir = lang_path();

        $items = collect(scandir($langDir));

        $languageFolders = $items->filter(function ($item) use ($langDir) {
            return is_dir("{$langDir}/{$item}") && !in_array($item, ['.', '..']);
        })
            ->filter(fn($item) => $item !== config('app.locale'));

        $languages = $languageFolders->values()->all();


        $languageFilesDirectory = lang_path() . '/' . config('app.locale');

        $filesInDirectory = collect(scandir($languageFilesDirectory));

        $fileNames = $filesInDirectory->filter(function ($item) use ($langDir) {
            return !in_array($item, ['.', '..']);
        })->map(function ($item) {
            return Str::beforeLast($item, '.');
        });

        return View::make('languini::index', [
            'fileNames' => $fileNames,
        ]);
    }
}