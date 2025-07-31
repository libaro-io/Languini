<?php

namespace LibaroIo\Languini\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class ListTranslationsController
{
    public function __invoke(Request $request): \Illuminate\Contracts\View\View
    {
        return View::make('languini::index', [
            'fileNames' => $this->getFilesForTranslations(),
            'translatableLanguages' => $this->getLanguages(),
            'translationKeys' => $this->getTranslationComparisonForFile($request->get('filename')),
        ]);
    }

    private function getLanguages(): array
    {
        $langDir = lang_path();

        $items = collect(scandir($langDir));

        $languageFolders = $items->filter(function ($item) use ($langDir) {
            return is_dir("{$langDir}/{$item}") && !in_array($item, ['.', '..']);
        })
            ->filter(fn($item) => $item !== config('app.locale'));

        return $languageFolders->values()->all();
    }

    private function getAllLanguages(): array
    {
        $langDir = lang_path();

        $items = collect(scandir($langDir));

        $languageFolders = $items->filter(function ($item) use ($langDir) {
            return is_dir("{$langDir}/{$item}") && !in_array($item, ['.', '..']);
        });

        return $languageFolders->values()->all();
    }

    private function getFilesForTranslations(): array
    {
        $languageFilesDirectory = lang_path() . '/' . config('app.locale');

        $filesInDirectory = collect(scandir($languageFilesDirectory));

        return $filesInDirectory->filter(function ($item) {
            return !in_array($item, ['.', '..']);
        })->map(function ($item) {
            return Str::beforeLast($item, '.');
        })->values()
            ->all();
    }

    private function getTranslationComparisonForFile(?string $filename): array
    {
        if (!$filename) {
            return [];
        }

        $languages = $this->getAllLanguages();
        $allTranslations = [];
        $uniqueKeys = [];

        foreach ($languages as $lang) {
            $filePath = lang_path("{$lang}/{$filename}.php");

            if (file_exists($filePath)) {
                $translations = require $filePath;
                $flattened = $this->flattenWithDots($translations);
                $allTranslations[$lang] = $flattened;

                foreach (array_keys($flattened) as $key) {
                    $uniqueKeys[$key] = true;
                }
            }
        }

        $masterKeys = array_keys($uniqueKeys);
        sort($masterKeys);

        $resultTable = [];
        foreach ($masterKeys as $key) {
            $row = ['key' => $key];
            foreach ($languages as $lang) {
                $row[$lang] = $allTranslations[$lang][$key] ?? ''; // when no translation is found, it displays the fallback
            }
            $resultTable[] = $row;
        }

        return $resultTable;
    }

    private function flattenWithDots(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $result += $this->flattenWithDots($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }
}