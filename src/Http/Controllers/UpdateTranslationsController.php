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

                // Merge translations, preserving empty arrays
                $mergedTranslations = $this->mergeTranslations($existingTranslations, $translations);

                $exportedArray = var_export($mergedTranslations, true);

                // Replace array() syntax with [] syntax, but preserve parentheses in string content
                $shortArraySyntax = str_replace('array (', '[', $exportedArray);
                
                // Only replace closing parentheses that are array closures, not those inside strings
                // Process character by character to track string boundaries
                $result = '';
                $length = strlen($shortArraySyntax);
                $insideString = false;
                $escapeNext = false;
                
                for ($i = 0; $i < $length; $i++) {
                    $char = $shortArraySyntax[$i];
                    
                    // Handle escape sequences
                    if ($escapeNext) {
                        $result .= $char;
                        $escapeNext = false;
                        continue;
                    }
                    
                    if ($char === '\\') {
                        $escapeNext = true;
                        $result .= $char;
                        continue;
                    }
                    
                    // Track string boundaries (var_export uses single quotes)
                    if ($char === "'") {
                        $insideString = !$insideString;
                        $result .= $char;
                        continue;
                    }
                    
                    // Only replace ')' if we're NOT inside a string
                    // In var_export output, any ')' outside a string is an array closure
                    if ($char === ')' && !$insideString) {
                        $result .= ']';
                    } else {
                        $result .= $char;
                    }
                }
                
                $shortArraySyntax = $result;
                
                // Replace double spaces with 4 spaces for indentation
                $shortArraySyntax = str_replace('  ', '    ', $shortArraySyntax);

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
                        // Convert '[]' string back to empty array
                        if ($translation === '[]' || (is_string($translation) && trim($translation) === '[]')) {
                            $translation = [];
                        }
                        data_set($result, "{$lang}.{$fileName}.{$currentKey}", $translation);
                    }
                }
            } elseif (is_array($value)) {
                // If it's an empty array, preserve it as an empty array
                if (empty($value)) {
                    // Preserve empty arrays for all languages
                    foreach ($availableLanguages as $lang) {
                        data_set($result, "{$lang}.{$fileName}.{$currentKey}", []);
                    }
                } else {
                    // If it's another array, recurse deeper.
                    $this->processKeys($value, $result, $fileName, $availableLanguages, $currentKey);
                }
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

    private function mergeTranslations(array $existing, array $new): array
    {
        $result = $existing;

        foreach ($new as $key => $value) {
            if (!isset($result[$key])) {
                // Key doesn't exist in existing, just set it
                $result[$key] = $value;
            } elseif (is_array($value) && is_array($result[$key])) {
                // Both are arrays, merge recursively
                if (empty($value)) {
                    // New value is empty array - preserve it
                    $result[$key] = [];
                } elseif (empty($result[$key])) {
                    // Existing is empty, use new value
                    $result[$key] = $value;
                } else {
                    // Both have values, merge recursively
                    $result[$key] = $this->mergeTranslations($result[$key], $value);
                }
            } else {
                // One or both are not arrays, replace with new value
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
