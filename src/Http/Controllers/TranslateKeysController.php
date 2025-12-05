<?php

namespace LibaroIo\Languini\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibaroIo\Languini\Services\LanguagesService;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponseChoice;

class TranslateKeysController
{
    public function __invoke(Request $request): JsonResponse
    {
        $baseLanguage = config('app.locale');
        $targetLanguages = LanguagesService::getLanguages();

        $mappedTargetLanguages = collect($targetLanguages)
            ->map(fn(string $language) => [$language => ['type' => 'string']]);

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4.1',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a translation assistant. Translate missing or empty values in the provided JSON structure from the base language "' . $baseLanguage . '" to the following target languages: ' . implode(', ', $targetLanguages) . '. IMPORTANT: Always use the "' . $baseLanguage . '" value as the source for translation. Do not translate the "' . $baseLanguage . '" values themselves - they are the source. CRITICAL: You must preserve ALL existing translations - do not change, modify, or remove any existing translation values. Only fill in missing or empty string translations. Return the complete structure with all existing translations intact and only new translations added for missing/empty values. When translating, preserve the exact format including colons, placeholders (like :amount), and spacing. Do not add any extra characters, numbers, or symbols that are not part of the translation.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->getStructuredLanguagesObject($request->get('filename')), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
            'functions' => [
                [
                    'name' => 'generated_translations',
                    'description' => 'Return an array of translated keys',
                    'parameters' => [
                        '$schema' => 'http://json-schema.org/draft-07/schema#',
                        'type' => 'object',
                        'properties' => [
                            'translations' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'key' => ['type' => 'string'],
                                        ...$mappedTargetLanguages->collapse()->toArray(),
                                    ],
                                    'required' => ['key', ...LanguagesService::getLanguages()],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'required' => ['translations'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'function_call' => ['name' => 'generated_translations'],
        ]);

        /** @var CreateResponseChoice $choice */
        $choice = $result->choices[0];
        $translatedValuesRaw = $choice->message->functionCall->arguments;
        
        // Ensure the string is properly UTF-8 encoded before decoding
        if (!mb_check_encoding($translatedValuesRaw, 'UTF-8')) {
            $translatedValuesRaw = mb_convert_encoding($translatedValuesRaw, 'UTF-8', mb_detect_encoding($translatedValuesRaw));
        }
        
        // Decode JSON with proper UTF-8 handling
        $responseAsArray = json_decode($translatedValuesRaw, true);
        
        // If decoding failed, log error but continue
        if ($responseAsArray === null && json_last_error() !== JSON_ERROR_NONE) {
            // Try one more time with forced UTF-8
            $translatedValuesRaw = mb_convert_encoding($translatedValuesRaw, 'UTF-8', 'UTF-8');
            $responseAsArray = json_decode($translatedValuesRaw, true);
        }
        
        // Ensure we have valid array structure
        if (!is_array($responseAsArray)) {
            $responseAsArray = [];
        }

        // Get the original data structure to preserve all existing translations
        $originalData = $this->getStructuredLanguagesObject($request->get('filename'));
        
        // Merge OpenAI response with original data to preserve ALL existing translations
        if (isset($responseAsArray['translations']) && is_array($responseAsArray['translations'])) {
            // Create a map of OpenAI translations by key
            $translatedMap = [];
            foreach ($responseAsArray['translations'] as $translation) {
                if (isset($translation['key'])) {
                    $translatedMap[$translation['key']] = $translation;
                }
            }
            
            // Merge with original data, ALWAYS preserving existing non-empty values
            $mergedTranslations = [];
            foreach ($originalData as $originalRow) {
                $key = $originalRow['key'] ?? null;
                if (!$key) {
                    continue;
                }
                
                // Start with the original row to preserve all existing translations
                $mergedRow = $originalRow;
                
                // Only add new translations from OpenAI if the original value is empty/missing
                if (isset($translatedMap[$key])) {
                    foreach ($translatedMap[$key] as $lang => $value) {
                        if ($lang !== 'key') {
                            // Skip null or empty values
                            if ($value === null || $value === '') {
                                continue;
                            }
                            
                            // Convert to string and ensure proper UTF-8 encoding
                            $translatedValue = (string) $value;
                            
                            // Ensure UTF-8 encoding and clean the value
                            if (!mb_check_encoding($translatedValue, 'UTF-8')) {
                                $translatedValue = mb_convert_encoding($translatedValue, 'UTF-8', 'UTF-8');
                            }
                            $translatedValue = trim($translatedValue);
                            
                            // Skip if the cleaned value is empty
                            if (empty($translatedValue)) {
                                continue;
                            }
                            
                            // Only update if original is empty or missing, preserve existing values
                            $originalValue = trim($mergedRow[$lang] ?? '');
                            if (empty($originalValue)) {
                                $mergedRow[$lang] = $translatedValue;
                            }
                        }
                    }
                }
                
                $mergedTranslations[] = $mergedRow;
            }
            
            $responseAsArray['translations'] = $mergedTranslations;
        } else {
            // If no translations from OpenAI, return original data
            $responseAsArray['translations'] = $originalData;
        }

        return response()->json($responseAsArray, 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function getStructuredLanguagesObject(string $filename): array
    {
        return LanguagesService::getTranslationComparisonForFile($filename);
    }
}