<?php

namespace LibaroIo\Languini\Http\Controllers;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UpdateTranslationsController
{
    public function __construct(
        protected readonly Filesystem $filesystem,
    )
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $submittedData = $request->except('_token');

        $fileName = Arr::get($submittedData, 'fileName');

        $availableLanguages = $this->getLanguages();

        $translationsByLang = [];

        foreach (Arr::get($submittedData, $fileName) as $fileName => $fileData) {
            if (!is_array($fileData)) {
                continue;
            }

            $this->processKeys($fileData, $translationsByLang, $fileName, $availableLanguages);
        }

        foreach ($translationsByLang as $lang => $files) {
            foreach ($files as $fileName => $translations) {
                $filePath = lang_path("{$lang}/{$fileName}.php");

                $this->filesystem->ensureDirectoryExists(dirname($filePath));

                $existingTranslations = [];
                if ($this->filesystem->exists($filePath)) {
                    $existingTranslations = require $filePath;
                }

                $mergedTranslations = array_replace_recursive($existingTranslations, $translations);

                $exportedArray = var_export($mergedTranslations, true);

                $shortArraySyntax = str_replace(['array (', ')', '  '], ['[', ']', '    '], $exportedArray);

                $outputContent = "<?php\n\nreturn " . $shortArraySyntax . ";\n";

                $this->filesystem->put($filePath, $outputContent);
            }
        }

        return redirect()
            ->route('languini.index', [
                'filename' => $request->get('filename'),
            ])->with('success', 'Translations updated successfully');
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

    private function processKeys(array $data, array &$result, string $fileName, array $availableLanguages, string $parentKey = ''): void
    {
        foreach ($data as $key => $value) {
            $currentKey = $parentKey ? "{$parentKey}.{$key}" : $key;

            if ($this->isTranslationArray($value, $availableLanguages)) {
                foreach ($value as $lang => $translation) {
                    if ($translation !== null && in_array($lang, $availableLanguages, true)) {
                        data_set($result, "{$lang}.{$fileName}.{$currentKey}", $translation);
                    }
                }
            } elseif (is_array($value)) {
                // If it's another array, recurse deeper.
                $this->processKeys($value, $result, $fileName, $availableLanguages, $currentKey);
            }
        }
    }

    private function isTranslationArray(mixed $value, array $availableLanguages): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        $keys = array_keys($value);

        return empty(array_diff($keys, $availableLanguages));
    }
}
