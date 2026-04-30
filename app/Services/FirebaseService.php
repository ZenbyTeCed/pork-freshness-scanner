<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private string $scansUrl;

    public function __construct()
    {
        $this->scansUrl = config(
            'services.firebase.scans_url',
            'https://porkyy-default-rtdb.asia-southeast1.firebasedatabase.app/scans.json'
        );
    }

    public function getLatestScan(): ?array
    {
        try {
            $response = Http::acceptJson()->timeout(10)->get($this->scansUrl);

            if ($response->forbidden() || $response->unauthorized()) {
                Log::warning('Firebase permission denied while fetching scans.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            if ($response->failed()) {
                Log::warning('Firebase returned an error while fetching scans.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $scans = $response->json();

            if (! is_array($scans) || $scans === []) {
                return null;
            }

            $validScans = collect($scans)
                ->filter(fn ($scan) => is_array($scan) && array_key_exists('created_at', $scan))
                ->sortByDesc(fn ($scan) => $this->sortableTimestamp($scan['created_at']))
                ->values();

            if ($validScans->isEmpty()) {
                Log::warning('Firebase scans response did not contain valid scan data.', [
                    'response' => $scans,
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
        return [
            'grade' => $scan['grade'] ?? null,
            'confidence' => isset($scan['confidence']) ? (float) $scan['confidence'] : null,
            'mq135' => isset($scan['mq135']) ? (float) $scan['mq135'] : null,
            'temperature' => isset($scan['temperature']) ? (float) $scan['temperature'] : null,
            'humidity' => isset($scan['humidity']) ? (float) $scan['humidity'] : null,
            'image_url' => $scan['image_url'] ?? null,
            'created_at' => $scan['created_at'] ?? null,
        ];
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
}
