<?php

namespace App\Services;

use App\Support\ScanImageUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private string $historyUrl;

    public function __construct()
    {
        $this->historyUrl = config(
            'services.firebase.history_url',
            'https://porkyy-default-rtdb.asia-southeast1.firebasedatabase.app/history.json'
        );
    }

    public function getLatestScan(): ?array
    {
        try {
            $response = Http::acceptJson()->timeout(10)->get($this->historyUrl);

            if ($response->forbidden() || $response->unauthorized()) {
                Log::warning('Firebase permission denied while fetching history.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            if ($response->failed()) {
                Log::warning('Firebase returned an error while fetching history.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $history = $response->json();

            if (! is_array($history) || $history === []) {
                return null;
            }

            $validScans = collect($history)
                ->filter(fn ($scan) => is_array($scan) && array_key_exists('timestamp', $scan))
                ->filter(fn ($scan) => ($scan['user_id'] ?? null) === session('firebase_uid'))
                ->sortByDesc(fn ($scan) => $this->sortableTimestamp($scan['timestamp']))
                ->values();

            if ($validScans->isEmpty()) {
                Log::warning('Firebase history response did not contain valid scan data.', [
                    'response' => $history,
                ]);

                return null;
            }

            return $this->formatScan($validScans->first());
        } catch (\Throwable $exception) {
            Log::error('Unable to fetch latest Firebase scan.', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function formatScan(array $scan): array
    {
        $classification = $this->normalizeClassification($scan['prediction'] ?? $scan['classification'] ?? null);

        return [
            'prediction' => $classification,
            'classification' => $classification,
            'source' => $scan['source'] ?? null,
            'confidence' => isset($scan['confidence']) ? (float) $scan['confidence'] : null,
            'gas' => $scan['gas'] ?? $scan['mq135'] ?? null,
            'mq135' => $scan['mq135'] ?? $scan['gas'] ?? null,
            'temperature' => isset($scan['temperature']) ? (float) $scan['temperature'] : null,
            'humidity' => $scan['humidity'] ?? null,
            'image_url' => $this->imageUrlFromScan($scan),
            'timestamp' => $scan['timestamp'] ?? null,
            'created_at' => $scan['timestamp'] ?? null,
        ];
    }

    private function imageUrlFromScan(array $scan): ?string
    {
        return ScanImageUrl::fromRecord($scan);
    }

    private function sortableTimestamp(mixed $createdAt): int
    {
        if (is_numeric($createdAt)) {
            return (int) $createdAt;
        }

        if (is_string($createdAt)) {
            $timestamp = strtotime($createdAt);

            return $timestamp ?: 0;
        }

        return 0;
    }

    private function normalizeClassification(mixed $classification): ?string
    {
        if (! is_string($classification)) {
            return null;
        }

        $classification = strtolower(trim($classification));

        return match ($classification) {
            'fresh' => 'fresh',
            'not_fresh' => 'not_fresh',
            default => null,
        };
    }
}
