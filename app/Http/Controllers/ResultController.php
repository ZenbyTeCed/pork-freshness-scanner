<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Kreait\Firebase\Factory;

class ResultController extends Controller
{
    protected $database;
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;

        $factory = (new Factory)
            ->withServiceAccount(base_path('firebase-credentials.json.json'))
            ->withDatabaseUri(config('services.firebase.database_url'));

        $this->database = $factory->createDatabase();
    }

    public function result(string $historyId)
    {
        $record = $this->database->getReference("history/{$historyId}")->getValue();

        if (! is_array($record) || ($record['user_id'] ?? null) !== session('firebase_uid')) {
            abort(404);
        }

        return view('pages.result', [
            'historyId' => $historyId,
            'result' => $this->formatHistoryRecord($historyId, $record),
        ]);
    }

    public function history()
    {
        $records = $this->database->getReference('history')->getValue();

        $historyItems = collect(is_array($records) ? $records : [])
            ->filter(fn ($record) => is_array($record) && ($record['user_id'] ?? null) === session('firebase_uid'))
            ->map(fn ($record, $id) => $this->formatHistoryRecord((string) $id, $record))
            ->sortByDesc(fn ($record) => strtotime($record['timestamp'] ?? '') ?: 0)
            ->values();

        return view('pages.history', [
            'historyItems' => $historyItems,
        ]);
    }

    public function latest()
    {
        $latestScan = $this->firebaseService->getLatestScan();

        if (! $latestScan) {
            return response()
                ->json(['message' => 'No scan data available'], 404)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        return response()
            ->json($latestScan)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function formatHistoryRecord(string $id, array $record): array
    {
        $prediction = $this->normalizePrediction($record['prediction'] ?? $record['classification'] ?? null);
        $grade = $record['grade'] ?? $this->gradeFromPrediction($prediction);
        $confidence = $this->normalizeConfidence($record['confidence'] ?? 0);

        return [
            'id' => $id,
            'source' => $record['source'] ?? 'upload',
            'source_label' => ($record['source'] ?? 'upload') === 'esp32' ? 'ESP32-CAM' : 'Uploaded Image',
            'analysis_type' => ($record['source'] ?? 'upload') === 'esp32' ? 'Image and Sensor Scan' : 'Image Classification Only',
            'image_url' => $record['image_url'] ?? asset('images/Porky Logo.png'),
            'prediction' => $prediction,
            'prediction_label' => $this->formatPrediction($prediction),
            'confidence' => $confidence,
            'confidence_label' => number_format($confidence, 1) . '%',
            'grade' => $grade,
            'grade_label' => 'Grade ' . ($grade ?: 'N/A'),
            'grade_class' => strtolower((string) $grade),
            'recommendation' => $record['recommendation'] ?? $this->recommendationFromGrade($grade),
            'timestamp' => $record['timestamp'] ?? $record['created_at'] ?? null,
            'date_label' => $this->formatTimestamp($record['timestamp'] ?? $record['created_at'] ?? null),
            'mq135' => $record['mq135'] ?? null,
            'temperature' => $record['temperature'] ?? null,
            'humidity' => $record['humidity'] ?? null,
            'device_id' => $record['device_id'] ?? null,
        ];
    }

    private function normalizePrediction(mixed $prediction): ?string
    {
        if (! is_string($prediction)) {
            return null;
        }

        $prediction = strtolower(trim($prediction));

        return in_array($prediction, ['fresh', 'half_fresh', 'spoiled'], true)
            ? $prediction
            : null;
    }

    private function normalizeConfidence(mixed $confidence): float
    {
        $value = is_numeric($confidence) ? (float) $confidence : 0;

        return $value <= 1 ? round($value * 100, 2) : round($value, 2);
    }

    private function gradeFromPrediction(?string $prediction): ?string
    {
        return match ($prediction) {
            'fresh' => 'A',
            'half_fresh' => 'B',
            'spoiled' => 'C',
            default => null,
        };
    }

    private function formatPrediction(?string $prediction): string
    {
        return match ($prediction) {
            'fresh' => 'Fresh',
            'half_fresh' => 'Half Fresh',
            'spoiled' => 'Spoiled',
            default => 'N/A',
        };
    }

    private function formatTimestamp(mixed $timestamp): string
    {
        if (! $timestamp) {
            return 'N/A';
        }

        $date = is_numeric($timestamp)
            ? date_create('@' . ((int) $timestamp > 10000000000 ? (int) ($timestamp / 1000) : (int) $timestamp))
            : date_create((string) $timestamp);

        return $date ? $date->format('M j, Y, g:i A') : 'N/A';
    }

    private function recommendationFromGrade(?string $grade): string
    {
        return match ($grade) {
            'A' => 'Excellent quality. Safe for immediate consumption or storage.',
            'B' => 'Acceptable quality. Keep refrigerated and cook thoroughly.',
            'C' => 'Poor quality indicators detected. Do not consume if spoilage is suspected.',
            default => 'Review the result before consumption.',
        };
    }
}
