<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiInsightService
{
    public function generateInsight(array $data): string
    {
        if (! config('services.gemini.api_key')) {
            return $this->fallbackInsight($data);
        }

        try {
            $response = Http::acceptJson()
                ->timeout(12)
                ->withHeaders([
                    'x-goog-api-key' => config('services.gemini.api_key'),
                ])
                ->post($this->endpoint(), [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $this->prompt($data)],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 160,
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('Gemini insight request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackInsight($data);
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if (! is_string($text) || trim($text) === '') {
                return $this->fallbackInsight($data);
            }

            return $this->shortenInsight($text);
        } catch (\Throwable $exception) {
            Log::warning('Gemini insight generation failed.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->fallbackInsight($data);
        }
    }

    private function endpoint(): string
    {
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    private function prompt(array $data): string
    {
        return 'You are an AI assistant for a pork freshness monitoring system. Only discuss pork freshness, food quality, freshness indicators, sensor readings, and safe handling recommendations. Do not answer unrelated questions. Based on the following result data, generate a short 2-4 sentence insight. Be cautious and do not claim absolute safety. Result data: '
            . json_encode($data, JSON_PRETTY_PRINT);
    }

    public function fallbackInsight(array $data): string
    {
        $prediction = $data['prediction'] ?? null;
        $label = $data['prediction_label'] ?? 'the selected freshness class';
        $confidence = $data['confidence_label'] ?? 'the recorded';
        $source = $data['source'] ?? 'upload';

        if ($prediction === 'spoiled') {
            $guidance = 'The pork may show signs of spoilage, so it should not be consumed.';
        } elseif ($prediction === 'half_fresh') {
            $guidance = 'The pork may have moderate freshness, so use caution and check it further before deciding what to do.';
        } elseif ($prediction === 'fresh') {
            $guidance = 'The pork appears fresh based on the available result, but it should still be handled properly and stored cold if not used soon.';
        } else {
            $guidance = 'The freshness result should be reviewed carefully before making any handling decision.';
        }

        if ($source === 'esp32') {
            $context = 'This result uses image prediction plus available sensor readings.';
        } else {
            $context = 'This result is based on uploaded image classification only, so sensor readings were not used.';
        }

        return "This sample was classified as {$label} with {$confidence} confidence. {$context} {$guidance} This AI result is only an aid and should not replace human judgment.";
    }

    private function shortenInsight(string $text): string
    {
        $cleanText = trim(preg_replace('/\s+/', ' ', strip_tags($text)));
        $sentences = preg_split('/(?<=[.!?])\s+/', $cleanText, -1, PREG_SPLIT_NO_EMPTY);

        if (! $sentences) {
            return $cleanText;
        }

        return implode(' ', array_slice($sentences, 0, 4));
    }
}
