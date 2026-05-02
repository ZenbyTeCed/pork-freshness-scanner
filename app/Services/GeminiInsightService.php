<?php

namespace App\Services;

class GeminiInsightService
{
    public function generateInsight(array $data): string
    {
        return $this->fallbackInsight($data);
    }

    public function fallbackInsight(array $data): string
    {
        $prediction = $data['prediction'] ?? null;
        $label = strtolower($data['prediction_label'] ?? 'the selected result');
        $confidenceValue = $this->normalizeConfidence($data['confidence'] ?? 0);
        $confidenceLabel = $this->formatConfidence($confidenceValue);
        $confidenceLevel = $this->confidenceLevel($confidenceValue);
        $source = $data['source'] ?? 'upload';

        $variations = match ($prediction) {
            'fresh' => [
                'The image result looks reassuring, but keep the sample chilled and handle it properly.',
                'This is a good sign from the model, especially if the meat also smells normal and was stored cold.',
                'The sample appears to be in acceptable condition based on the image, but normal food safety checks still matter.',
            ],
            'not_fresh' => [
                'The image shows signs that need caution, so it is better to inspect the sample further before using it.',
                'This result suggests the pork may be past its best condition, especially if there are odor or texture changes.',
                'Treat this as a warning result and check the sample carefully before making any handling decision.',
            ],
            default => [
                'The result needs a careful review before making any handling decision.',
            ],
        };

        $sourceNote = $source === 'esp32'
            ? 'This insight also considers that the scan came from the ESP32 workflow.'
            : 'This insight is based on the uploaded image classification.';

        $firstSentence = "This sample was classified as {$label} with {$confidenceLabel} confidence, which is a {$confidenceLevel} result.";
        $secondSentence = $variations[array_rand($variations)];

        return "{$firstSentence} {$secondSentence} {$sourceNote}";
    }

    private function normalizeConfidence(mixed $confidence): float
    {
        $value = is_numeric($confidence) ? (float) $confidence : 0;

        return $value > 1 ? $value / 100 : $value;
    }

    private function formatConfidence(float $confidence): string
    {
        return number_format($confidence * 100, 0) . '%';
    }

    private function confidenceLevel(float $confidence): string
    {
        if ($confidence > 0.9) {
            return 'high confidence';
        }

        if ($confidence >= 0.7) {
            return 'moderate confidence';
        }

        return 'lower confidence';
    }
}
