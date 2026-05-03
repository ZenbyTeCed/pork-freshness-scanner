<?php

namespace App\Http\Controllers;

use App\Services\FirebaseDatabaseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    private const DEVICE_ID = 'esp32cam_01';

    protected $database;

    public function __construct(FirebaseDatabaseFactory $firebase)
    {
        $this->database = $firebase->create();
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'prediction' => 'required|string|in:fresh,not_fresh',
            'confidence' => 'required|numeric|min:0|max:100',
            'source' => 'nullable|string|in:upload,camera',
        ]);

        $prediction = $request->string('prediction')->toString();
        $confidence = $this->normalizeConfidence((float) $request->confidence);
        $source = $request->input('source', 'upload');
        $timestamp = now()->toIso8601String();

        // Store the original uploaded image so the result page can show what was analyzed.
        $imagePath = $request->file('image')->store('scans', 'public');

        $historyRef = $this->database->getReference('history')->push([
            'user_id' => session('firebase_uid'),
            'source' => $source,
            'image_path' => $imagePath,
            'image_url' => '/storage/' . $imagePath,
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
        $requestedAt = now()->toIso8601String();
        $requestedBy = session('firebase_uid');

        if (! is_string($requestedBy) || trim($requestedBy) === '') {
            return response()->json([
                'message' => 'You must be logged in before requesting an ESP32 capture.',
            ], 401);
        }

        $command = [
            'command' => 'capture',
            'command_id' => $commandId,
            'status' => 'pending',
            'requested_at' => $requestedAt,
            'requested_by' => $requestedBy,
        ];

        // The ESP32 watches this command path, then writes the scan result to history/.
        $this->database->getReference($commandPath)->set($command);
        $savedCommand = $this->database->getReference($commandPath)->getValue();

        Log::info('ESP32 capture command written.', [
            'path' => $commandPath,
            'command_id' => $commandId,
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

        $historyResult = $this->waitForEsp32HistoryResult($commandId, $requestedBy, $requestedAt);

        if (! $historyResult) {
            return response()->json([
                'message' => 'No matching ESP32 result was received yet. Please wait until the device saves this command_id and requested_by with the scan result.',
            ], 504);
        }

        $historyId = $historyResult['id'];
        $historyRecord = $historyResult['record'];
        $prediction = $this->normalizePrediction($historyRecord['prediction'] ?? $historyRecord['classification'] ?? null);

        if (! $prediction) {
            return response()->json([
                'message' => 'The ESP32 result did not include a valid prediction.',
            ], 422);
        }

        $this->prepareEsp32HistoryRecordForResult($historyId, $historyRecord, $deviceId, $prediction, $commandId, $requestedBy);

        return response()->json([
            'history_id' => $historyId,
            'redirect_url' => route('result', ['historyId' => $historyId]),
        ]);
    }

    private function waitForEsp32HistoryResult(string $commandId, string $requestedBy, string $requestedAt): ?array
    {
        $deadline = microtime(true) + 60;

        while (microtime(true) < $deadline) {
            $result = $this->findEsp32HistoryResult($commandId, $requestedBy, $requestedAt);

            if ($result) {
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

    private function findEsp32HistoryResult(string $commandId, string $requestedBy, string $requestedAt): ?array
    {
        $path = 'history';
        $value = $this->database->getReference($path)->getValue();
        $historyItems = collect(is_array($value) ? $value : [])
            ->filter(fn ($record) => is_array($record))
            ->map(fn ($record, $id) => [
                'id' => (string) $id,
                'record' => $record,
            ])
            ->values();

        $match = $historyItems
            ->filter(fn ($item) => $this->isMatchingEsp32HistoryRecord($item['record'], $commandId, $requestedBy))
            ->first();
        $prediction = $match
            ? $this->normalizePrediction($match['record']['prediction'] ?? $match['record']['classification'] ?? null)
            : null;

        Log::debug('ESP32 history polling check.', [
            'path' => $path,
            'command_id' => $commandId,
            'requested_by' => $requestedBy,
            'requested_at' => $requestedAt,
            'history_count' => is_array($value) ? count($value) : 0,
            'matching_history_found' => $match !== null,
            'matching_history_id' => $match['id'] ?? null,
            'matching_history_has_command_id' => $match ? array_key_exists('command_id', $match['record']) : false,
            'matching_history_has_requested_by' => $match ? array_key_exists('requested_by', $match['record']) : false,
            'has_prediction' => $prediction !== null,
            'prediction' => $prediction,
        ]);

        if (! $match || ! $prediction) {
            return null;
        }

        return $match;
    }

    private function hasValidPrediction(array $record): bool
    {
        return $this->normalizePrediction($record['prediction'] ?? $record['classification'] ?? null) !== null;
    }

    private function isMatchingEsp32HistoryRecord(array $record, string $commandId, string $requestedBy): bool
    {
        if (! $this->hasValidPrediction($record)) {
            return false;
        }

        return ($record['command_id'] ?? null) === $commandId
            && ($record['requested_by'] ?? null) === $requestedBy;
    }

    private function prepareEsp32HistoryRecordForResult(
        string $historyId,
        array $record,
        string $deviceId,
        string $prediction,
        string $commandId,
        string $requestedBy
    ): void
    {
        $updates = [
            'command_id' => $commandId,
            'requested_by' => $requestedBy,
            'user_id' => $requestedBy,
        ];

        if (! array_key_exists('source', $record)) {
            $updates['source'] = 'esp32';
        }

        if (! array_key_exists('device_id', $record)) {
            $updates['device_id'] = $record['user_id'] ?? $deviceId;
        }

        if (! array_key_exists('recommendation', $record)) {
            $updates['recommendation'] = $this->recommendationFromPrediction($prediction);
        }

        if (! array_key_exists('humidity', $record)) {
            $updates['humidity'] = 'N/A';
        }

        if ($updates !== []) {
            $this->database->getReference("history/{$historyId}")->update($updates);

            Log::info('ESP32 history record prepared for result page.', [
                'history_id' => $historyId,
                'updates' => $updates,
            ]);
        }
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

    private function normalizeConfidence(float $confidence): float
    {
        return $confidence <= 1 ? round($confidence * 100, 2) : round($confidence, 2);
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
