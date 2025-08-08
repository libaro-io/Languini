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
            'model' => 'gpt-5-nano',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a translation assistant. Translate missing values in the provided JSON structure to the following languages: ' . implode(', ', $targetLanguages) . '. Do not translate the "' . $baseLanguage . '" values. Keep all existing translations as-is. Return the structure in valid JSON, with the same array format. Only fill in missing or empty translations. Do not change existing translations.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->getStructuredLanguagesObject($request->get('filename')), JSON_PRETTY_PRINT),
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
        $responseAsArray = json_decode($translatedValuesRaw, true);

        return response()->json($responseAsArray);
    }

    private function getStructuredLanguagesObject(string $filename): array
    {
        return LanguagesService::getTranslationComparisonForFile($filename);
    }
}