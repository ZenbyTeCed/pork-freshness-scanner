<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use App\Services\GeminiInsightService;
use Kreait\Firebase\Factory;

class ResultController extends Controller
{
    protected $database;
    protected FirebaseService $firebaseService;
    protected GeminiInsightService $geminiInsightService;

    public function __construct(FirebaseService $firebaseService, GeminiInsightService $geminiInsightService)
    {
        $this->firebaseService = $firebaseService;
        $this->geminiInsightService = $geminiInsightService;

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

        $result = $this->formatHistoryRecord($historyId, $record);
        $result['ai_insight'] = $this->geminiInsightService->fallbackInsight($this->geminiData($result));

        return view('pages.result', [
            'historyId' => $historyId,
            'result' => $result,
        ]);
    }

    public function generateAiInsight(string $historyId)
    {
        $record = $this->database->getReference("history/{$historyId}")->getValue();

        if (! is_array($record) || ($record['user_id'] ?? null) !== session('firebase_uid')) {
            abort(404);
        }

        $result = $this->formatHistoryRecord($historyId, $record);
        $data = $this->geminiData($result);

        return response()->json([
            'insight' => $this->geminiInsightService->generateInsight($data),
        ]);
    }

    public function dashboard()
    {
        $historyItems = $this->getUserHistoryItems();
        $totalScans = $historyItems->count();
        $today = now()->toDateString();
        $scansToday = $historyItems
            ->filter(fn ($record) => $this->dateKey($record['timestamp']) === $today)
            ->count();
        $averageConfidence = $totalScans > 0
            ? round($historyItems->avg('confidence'), 1)
            : 0;
        $gradeCounts = [
            $historyItems->where('grade', 'A')->count(),
            $historyItems->where('grade', 'B')->count(),
            $historyItems->where('grade', 'C')->count(),
        ];
        $gradeLabels = ['Grade A', 'Grade B', 'Grade C'];
        $gradeSummary = collect($gradeLabels)
            ->map(function ($label, $index) use ($gradeCounts, $totalScans) {
                $count = $gradeCounts[$index];

                return [
                    'label' => $label,
                    'count' => $count,
                    'percent' => $totalScans > 0 ? round(($count / $totalScans) * 100) : 0,
                ];
            });

        return view('pages.dashboard', [
            'totalScans' => $totalScans,
            'scansToday' => $scansToday,
            'averageConfidence' => $averageConfidence,
            'gradeCounts' => $gradeCounts,
            'gradeLabels' => $gradeLabels,
            'gradeSummary' => $gradeSummary,
            'recentActivities' => $historyItems->take(5),
        ]);
    }

    public function history()
    {
        $historyItems = $this->getUserHistoryItems();

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

    private function getUserHistoryItems()
    {
        $records = $this->database->getReference('history')->getValue();

        return collect(is_array($records) ? $records : [])
            ->filter(fn ($record) => is_array($record) && ($record['user_id'] ?? null) === session('firebase_uid'))
            ->map(fn ($record, $id) => $this->formatHistoryRecord((string) $id, $record))
            ->sortByDesc(fn ($record) => strtotime($record['timestamp'] ?? '') ?: 0)
            ->values();
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
            'search_text' => $this->historySearchText($id, $record, $prediction, $grade, $confidence),
        ];
    }

    private function historySearchText(string $id, array $record, ?string $prediction, ?string $grade, float $confidence): string
    {
        $source = $record['source'] ?? 'upload';
        $sourceLabel = $source === 'esp32' ? 'ESP32-CAM' : 'Uploaded Image';
        $predictionLabel = $this->formatPrediction($prediction);
        $timestamp = $record['timestamp'] ?? $record['created_at'] ?? null;
        $dateLabel = $this->formatTimestamp($timestamp);
        $aliases = [
            $prediction,
            str_replace('_', ' ', (string) $prediction),
            $source,
            str_replace('-', ' ', $sourceLabel),
            $grade,
            'grade ' . $grade,
            number_format($confidence, 1) . '%',
            (string) ($record['device_id'] ?? ''),
        ];

        return collect([
            $id,
            $sourceLabel,
            $predictionLabel,
            $dateLabel,
            ...$aliases,
        ])
            ->filter()
            ->implode(' ');
    }

    private function geminiData(array $result): array
    {
        $data = [
            'prediction' => $result['prediction'],
            'prediction_label' => $result['prediction_label'],
            'confidence' => $result['confidence'],
            'confidence_label' => $result['confidence_label'],
            'source' => $result['source'],
            'analysis_type' => $result['analysis_type'],
        ];

        if ($result['source'] === 'esp32') {
            $data['mq135'] = $result['mq135'];
            $data['temperature'] = $result['temperature'];
            $data['humidity'] = $result['humidity'];
        }

        return $data;
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

    private function dateKey(mixed $timestamp): ?string
    {
        if (! $timestamp) {
            return null;
        }

        $date = is_numeric($timestamp)
            ? date_create('@' . ((int) $timestamp > 10000000000 ? (int) ($timestamp / 1000) : (int) $timestamp))
            : date_create((string) $timestamp);

        return $date ? $date->format('Y-m-d') : null;
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
