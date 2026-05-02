<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        $commandId = 'capture_' . Str::uuid()->toString();
        $commandPath = "commands/{$deviceId}";
        $requestedAt = now()->toISOString();
        $requestedBy = session('firebase_uid') ?: 'web';
        $previousMeatResult = $this->latestEsp32ResultFromMeat();

        $command = [
            'command' => 'capture',
            'command_id' => $commandId,
            'status' => 'pending',
            'requested_at' => $requestedAt,
            'requested_by' => $requestedBy,
        ];

        // The ESP32 watches this command path, then writes the scan result to meat/.
        $this->database->getReference($commandPath)->set($command);
        $savedCommand = $this->database->getReference($commandPath)->getValue();

        Log::info('ESP32 capture command written.', [
            'path' => $commandPath,
            'command' => $command,
            'saved_command' => $savedCommand,
            'is_pending_capture' => ($savedCommand['status'] ?? null) === 'pending'
                && ($savedCommand['command'] ?? null) === 'capture',
        ]);

        if (! $this->isSavedCaptureCommand($savedCommand, $commandId)) {
            Log::error('ESP32 capture command was not saved correctly.', [
                'path' => $commandPath,
                'expected_command' => $command,
                'saved_command' => $savedCommand,
            ]);

            return response()->json([
                'message' => 'The ESP32 capture command could not be saved. Please try again.',
            ], 500);
        }

        $esp32Result = $this->waitForEsp32Result($deviceId, $commandId, $previousMeatResult);

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

    private function waitForEsp32Result(string $deviceId, string $commandId, ?array $previousMeatResult): ?array
    {
        $startedAt = now();
        $deadline = microtime(true) + 15;
        $previousFingerprint = $this->resultFingerprint($previousMeatResult);

        while (microtime(true) < $deadline) {
            $result = $this->latestEsp32ResultFromMeat();

            if ($result && $this->isFreshEsp32Result($result, $startedAt, $commandId, $previousFingerprint)) {
                return $result;
            }

            usleep(750000);
        }

        return null;
    }

    private function isSavedCaptureCommand(mixed $savedCommand, string $commandId): bool
    {
        return is_array($savedCommand)
            && ($savedCommand['command'] ?? null) === 'capture'
            && ($savedCommand['command_id'] ?? null) === $commandId
            && ($savedCommand['status'] ?? null) === 'pending';
    }

    private function latestEsp32ResultFromMeat(): ?array
    {
        $path = 'meat';
        $value = $this->database->getReference($path)->getValue();
        $result = $this->pickLatestMeatResult($value);
        $prediction = is_array($result)
            ? $this->normalizePrediction($result['prediction'] ?? $result['classification'] ?? null)
            : null;
        $status = is_array($result)
            ? $this->normalizeStatus($result['status'] ?? null)
            : null;

        Log::debug('ESP32 Firebase result polling check.', [
            'path' => $path,
            'firebase_data' => $value,
            'latest_result' => $result,
            'has_prediction' => $prediction !== null,
            'prediction' => $prediction,
            'has_done_status' => $status === 'done',
            'status' => $status,
        ]);

        if (! $result || ! $prediction || $status !== 'done') {
            return null;
        }

        return $result;
    }

    private function pickLatestMeatResult(mixed $value): ?array
    {
        if (! is_array($value) || $value === []) {
            return null;
        }

        if ($this->looksLikeScanResult($value)) {
            return $value;
        }

        return collect($value)
            ->filter(fn ($scan) => is_array($scan) && $this->looksLikeScanResult($scan))
            ->sortByDesc(fn ($scan) => $this->sortableTimestamp($scan['timestamp'] ?? $scan['created_at'] ?? null))
            ->first();
    }

    private function looksLikeScanResult(array $value): bool
    {
        return array_key_exists('prediction', $value)
            || array_key_exists('classification', $value)
            || array_key_exists('status', $value);
    }

    private function isFreshEsp32Result(array $result, Carbon $startedAt, string $commandId, ?string $previousFingerprint): bool
    {
        if (($result['command_id'] ?? null) === $commandId) {
            return true;
        }

        $timestamp = $result['timestamp'] ?? $result['created_at'] ?? null;

        if (! $timestamp) {
            return $this->resultFingerprint($result) !== $previousFingerprint;
        }

        return Carbon::parse($timestamp)->greaterThanOrEqualTo($startedAt->copy()->subSeconds(2));
    }

    private function resultFingerprint(?array $result): ?string
    {
        if (! $result) {
            return null;
        }

        ksort($result);

        return md5(json_encode($result));
    }

    private function normalizePrediction(mixed $prediction): ?string
    {
        if (! is_string($prediction)) {
            return null;
        }

        $prediction = strtolower(trim($prediction));
        $prediction = str_replace([' ', '-'], '_', $prediction);

        return match ($prediction) {
            'fresh' => 'fresh',
            'not_fresh' => 'not_fresh',
            default => null,
        };
    }

    private function normalizeStatus(mixed $status): ?string
    {
        return is_string($status) ? strtolower(trim($status)) : null;
    }

    private function sortableTimestamp(mixed $timestamp): int
    {
        if (is_numeric($timestamp)) {
            return (int) $timestamp;
        }

        if (is_string($timestamp)) {
            return strtotime($timestamp) ?: 0;
        }

        return 0;
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
