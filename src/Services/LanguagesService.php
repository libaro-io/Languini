<?php

namespace LibaroIo\Languini\Services;

use Illuminate\Support\Str;

class LanguagesService
{
    public static function getLanguages(): array
    {
        $langDir = lang_path();

        $items = collect(scandir($langDir));

        $languageFolders = $items->filter(function ($item) use ($langDir) {
            return is_dir("{$langDir}/{$item}") && !in_array($item, ['.', '..']);
        })
            ->filter(fn($item) => $item !== config('app.locale'));

        return $languageFolders->values()->all();
    }

    public static function getAllLanguages(): array
    {
        $langDir = lang_path();

        $items = collect(scandir($langDir));

        $languageFolders = $items->filter(function ($item) use ($langDir) {
            return is_dir("{$langDir}/{$item}") && !in_array($item, ['.', '..']);
        });

        return $languageFolders->values()->all();
    }

    public static function getFilesForTranslations(): array
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

    public static function getTranslationComparisonForFile(?string $filename): array
    {
        if (!$filename) {
            return [];
        }

        $languages = self::getAllLanguages();
        $allTranslations = [];
        $uniqueKeys = [];

        foreach ($languages as $lang) {
            $filePath = lang_path("{$lang}/{$filename}.php");

            if (file_exists($filePath)) {
                $translations = require $filePath;
                $flattened = self::flattenWithDots($translations);
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

    public static function flattenWithDots(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (empty($value)) {
                    // Preserve empty arrays as empty arrays
                    $result[$prefix . $key] = [];
                } else {
                    // Recurse into non-empty arrays
                    $result += self::flattenWithDots($value, $prefix . $key . '.');
                }
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }
}