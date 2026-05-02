<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Factory;

class ScanController extends Controller
{
    private const DEVICE_ID = 'esp32cam_01';

    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path('firebase-credentials.json.json'))
            ->withDatabaseUri(config('services.firebase.database_url'));

        $this->database = $factory->createDatabase();
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'prediction' => 'required|string|in:fresh,not_fresh',
            'confidence' => 'required|numeric|min:0|max:100',
        ]);

        $prediction = $request->string('prediction')->toString();
        $confidence = $this->normalizeConfidence((float) $request->confidence);
        $timestamp = now()->toISOString();

        // Store the original uploaded image so the result page can show what was analyzed.
        $imagePath = $request->file('image')->store('scans', 'public');

        $historyRef = $this->database->getReference('history')->push([
            'user_id' => session('firebase_uid'),
            'source' => 'upload',
            'image_path' => $imagePath,
            'image_url' => asset('storage/' . $imagePath),
            'prediction' => $prediction,
            'confidence' => $confidence,
            'recommendation' => $this->recommendationFromPrediction($prediction),
            'timestamp' => $timestamp,
            'mq135' => null,
            'temperature' => null,
            'humidity' => null,
            'device_id' => null,
        ]);

        $historyId = $historyRef->getKey();

        return response()->json([
            'history_id' => $historyId,
            'redirect_url' => route('result', ['historyId' => $historyId]),
        ]);
    }

    public function captureEsp32(Request $request)
    {
        $deviceId = $request->input('device_id', self::DEVICE_ID);
        $commandId = uniqid('capture_', true);

        // The ESP32 should watch this path and write its latest result back to Firebase.
        $this->database->getReference("commands/{$deviceId}")->set([
            'command' => 'capture',
            'command_id' => $commandId,
            'requested_by' => session('firebase_uid'),
            'requested_at' => now()->toISOString(),
            'status' => 'pending',
        ]);

        $esp32Result = $this->waitForEsp32Result($deviceId, $commandId);

        if (! $esp32Result) {
            return response()->json([
                'message' => 'No ESP32 result was received yet. Please try again after the device finishes scanning.',
            ], 504);
        }

        $prediction = $this->normalizePrediction(
            $esp32Result['prediction'] ?? $esp32Result['classification'] ?? null
        );

        if (! $prediction) {
            return response()->json([
                'message' => 'The ESP32 result did not include a valid prediction.',
            ], 422);
        }

        $confidence = $this->normalizeConfidence((float) ($esp32Result['confidence'] ?? 0));
        $timestamp = $esp32Result['timestamp'] ?? $esp32Result['created_at'] ?? now()->toISOString();

        $historyRef = $this->database->getReference('history')->push([
            'user_id' => session('firebase_uid'),
            'source' => 'esp32',
            'image_url' => $esp32Result['image_url'] ?? null,
            'prediction' => $prediction,
            'confidence' => $confidence,
            'recommendation' => $esp32Result['recommendation'] ?? $this->recommendationFromPrediction($prediction),
            'timestamp' => $timestamp,
            'mq135' => $this->nullableFloat($esp32Result['mq135'] ?? null),
            'temperature' => $this->nullableFloat($esp32Result['temperature'] ?? null),
            'humidity' => $this->nullableFloat($esp32Result['humidity'] ?? null),
            'device_id' => $esp32Result['device_id'] ?? $deviceId,
        ]);

        $historyId = $historyRef->getKey();

        return response()->json([
            'history_id' => $historyId,
            'redirect_url' => route('result', ['historyId' => $historyId]),
        ]);
    }

    private function waitForEsp32Result(string $deviceId, string $commandId): ?array
    {
        $startedAt = now();
        $deadline = microtime(true) + 15;

        while (microtime(true) < $deadline) {
            $result = $this->latestEsp32Result($deviceId);

            if ($result && $this->isFreshEsp32Result($result, $startedAt, $commandId)) {
                return $result;
            }

            usleep(750000);
        }

        return null;
    }

    private function latestEsp32Result(string $deviceId): ?array
    {
        $paths = [
            "esp32_results/{$deviceId}/latest",
            "devices/{$deviceId}/latest_scan",
        ];

        foreach ($paths as $path) {
            $value = $this->database->getReference($path)->getValue();

            if (is_array($value) && $value !== []) {
                return $value;
            }
        }

        $scans = $this->database->getReference('scans')->getValue();

        if (! is_array($scans)) {
            return null;
        }

        return collect($scans)
            ->filter(fn ($scan) => is_array($scan))
            ->filter(fn ($scan) => ($scan['source'] ?? 'esp32') === 'esp32')
            ->sortByDesc(fn ($scan) => strtotime($scan['timestamp'] ?? $scan['created_at'] ?? '') ?: 0)
            ->first();
    }

    private function isFreshEsp32Result(array $result, Carbon $startedAt, string $commandId): bool
    {
        if (($result['command_id'] ?? null) === $commandId) {
            return true;
        }

        $timestamp = $result['timestamp'] ?? $result['created_at'] ?? null;

        if (! $timestamp) {
            return false;
        }

        return Carbon::parse($timestamp)->greaterThanOrEqualTo($startedAt->copy()->subSeconds(2));
    }

    private function normalizePrediction(mixed $prediction): ?string
    {
        if (! is_string($prediction)) {
            return null;
        }

        $prediction = strtolower(trim($prediction));

        return match ($prediction) {
            'fresh' => 'fresh',
            'not_fresh' => 'not_fresh',
            default => null,
        };
    }

    private function normalizeConfidence(float $confidence): float
    {
        return $confidence <= 1 ? round($confidence * 100, 2) : round($confidence, 2);
    }

    private function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function recommendationFromPrediction(string $prediction): string
    {
        return match ($prediction) {
            'fresh' => 'The sample appears fresh.',
            'not_fresh' => 'The sample may no longer be fresh. Further checking is recommended.',
            default => 'Review the result before making a handling decision.',
        };
    }
}
