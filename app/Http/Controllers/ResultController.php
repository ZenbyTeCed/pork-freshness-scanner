<?php

namespace App\Http\Controllers;

use App\Services\FirebaseDatabaseFactory;
use App\Services\FirebaseService;
use App\Services\GeminiInsightService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    protected $database;
    protected FirebaseService $firebaseService;
    protected GeminiInsightService $geminiInsightService;

    public function __construct(
        FirebaseService $firebaseService,
        GeminiInsightService $geminiInsightService,
        FirebaseDatabaseFactory $firebase
    )
    {
        $this->firebaseService = $firebaseService;
        $this->geminiInsightService = $geminiInsightService;
        $this->database = $firebase->create();
    }

    public function result(string $historyId)
    {
        $record = $this->database->getReference("history/{$historyId}")->getValue();

        if (! is_array($record) || ! $this->canUseHistoryRecord($record)) {
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

        if (! is_array($record) || ! $this->canUseHistoryRecord($record)) {
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
        $predictionCounts = [
            $historyItems->where('prediction', 'fresh')->count(),
            $historyItems->where('prediction', 'not_fresh')->count(),
        ];
        $predictionLabels = ['Fresh', 'Not Fresh'];
        $predictionSummary = collect($predictionLabels)
            ->map(function ($label, $index) use ($predictionCounts, $totalScans) {
                $count = $predictionCounts[$index];

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
            'predictionCounts' => $predictionCounts,
            'predictionLabels' => $predictionLabels,
            'predictionSummary' => $predictionSummary,
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

    public function deleteHistory(Request $request)
    {
        $validated = $request->validate([
            'history_ids' => ['required', 'array', 'min:1'],
            'history_ids.*' => ['required', 'string'],
        ]);

        $ids = collect($validated['history_ids'])
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['message' => 'Please select at least one history record.'], 422);
        }

        $deletedCount = 0;

        foreach ($ids as $id) {
            $record = $this->database->getReference("history/{$id}")->getValue();

            if (! is_array($record) || ! $this->canUseHistoryRecord($record)) {
                continue;
            }

            $this->database->getReference("history/{$id}")->remove();
            $deletedCount++;
        }

        return response()->json([
            'message' => $deletedCount === 1
                ? 'Deleted 1 history record.'
                : "Deleted {$deletedCount} history records.",
            'deleted_count' => $deletedCount,
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

    public function testFirebase()
    {
        return response()->json($this->firebaseService->getLatestScan());
    }

    private function getUserHistoryItems()
    {
        $records = $this->database->getReference('history')->getValue();

        return collect(is_array($records) ? $records : [])
            ->filter(fn ($record) => is_array($record) && $this->canUseHistoryRecord($record))
            ->map(fn ($record, $id) => $this->formatHistoryRecord((string) $id, $record))
            ->sortByDesc(fn ($record) => $record['sort_value'])
            ->values();
    }

    private function canUseHistoryRecord(array $record): bool
    {
        if (($record['user_id'] ?? null) === session('firebase_uid')) {
            return true;
        }

        return $this->looksLikeEsp32Record($record);
    }

    private function looksLikeEsp32Record(array $record): bool
    {
        $source = strtolower((string) ($record['source'] ?? ''));

        return $source === 'esp32'
            || array_key_exists('device_id', $record)
            || array_key_exists('command_id', $record)
            || array_key_exists('mq135', $record)
            || array_key_exists('gas', $record)
            || array_key_exists('temperature', $record)
            || array_key_exists('humidity', $record);
    }

    private function formatHistoryRecord(string $id, array $record): array
    {
        $prediction = $this->normalizePrediction($record['prediction'] ?? $record['classification'] ?? null);
        $confidence = $this->normalizeConfidence($record['confidence'] ?? 0);
        $predictionClass = $prediction ?: 'unknown';
        $source = $this->sourceFromRecord($record);

        return [
            'id' => $id,
            'source' => $source,
            'source_label' => $this->sourceLabel($source),
            'analysis_type' => $this->analysisType($source),
            'image_url' => $this->imageUrlFromRecord($record),
            'prediction' => $prediction,
            'prediction_label' => $this->formatPrediction($prediction),
            'prediction_class' => $predictionClass,
            'result_message' => $this->messageFromPrediction($prediction),
            'confidence' => $confidence,
            'confidence_label' => number_format($confidence, 1) . '%',
            'recommendation' => $this->messageFromPrediction($prediction),
            'timestamp' => $record['timestamp'] ?? $record['created_at'] ?? null,
            'sort_value' => $this->historySortValue($id, $record),
            'date_label' => $this->formatTimestamp($record['timestamp'] ?? $record['created_at'] ?? null),
            'gas' => $record['gas'] ?? $record['mq135'] ?? null,
            'mq135' => $record['mq135'] ?? $record['gas'] ?? null,
            'temperature' => $record['temperature'] ?? null,
            'humidity' => $record['humidity'] ?? null,
            'device_id' => $record['device_id'] ?? ($source === 'esp32' ? ($record['user_id'] ?? null) : null),
            'search_text' => $this->historySearchText($id, $record, $prediction, $confidence),
        ];
    }

    private function imageUrlFromRecord(array $record): string
    {
        $imageUrl = $record['image_url'] ?? null;

        if (is_string($imageUrl) && trim($imageUrl) !== '' && strtoupper(trim($imageUrl)) !== 'N/A') {
            return $imageUrl;
        }

        $imagePath = $record['image_path'] ?? null;

        if (is_string($imagePath) && trim($imagePath) !== '' && strtoupper(trim($imagePath)) !== 'N/A') {
            return asset('storage/' . $imagePath);
        }

        return '/images/Porky%20Logo.png';
    }

    private function sourceFromRecord(array $record): string
    {
        if (($record['source'] ?? null) === 'esp32' || $this->looksLikeEsp32Record($record)) {
            return 'esp32';
        }

        if (($record['source'] ?? null) === 'camera') {
            return 'camera';
        }

        return 'upload';
    }

    private function sourceLabel(string $source): string
    {
        return match ($source) {
            'esp32' => 'ESP32-CAM',
            'camera' => 'Device Camera',
            default => 'Uploaded Image',
        };
    }

    private function analysisType(string $source): string
    {
        return match ($source) {
            'esp32' => 'Image and Sensor Scan',
            'camera' => 'Device Camera Classification',
            default => 'Image Classification Only',
        };
    }

    private function historySearchText(string $id, array $record, ?string $prediction, float $confidence): string
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
            $data['gas'] = $result['gas'];
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
        $prediction = str_replace([' ', '-'], '_', $prediction);

        return match ($prediction) {
            'fresh' => 'fresh',
            'not_fresh' => 'not_fresh',
            default => null,
        };
    }

    private function normalizeConfidence(mixed $confidence): float
    {
        $value = is_numeric($confidence) ? (float) $confidence : 0;

        return $value <= 1 ? round($value * 100, 2) : round($value, 2);
    }

    private function formatPrediction(?string $prediction): string
    {
        return match ($prediction) {
            'fresh' => 'Fresh',
            'not_fresh' => 'Not Fresh',
            default => 'N/A',
        };
    }

    private function messageFromPrediction(?string $prediction): string
    {
        return match ($prediction) {
            'fresh' => 'The sample appears fresh.',
            'not_fresh' => 'The sample may no longer be fresh. Further checking is recommended.',
            default => 'Review the result before making a handling decision.',
        };
    }

    private function formatTimestamp(mixed $timestamp): string
    {
        if (! $timestamp) {
            return 'N/A';
        }

        if (is_numeric($timestamp) && (int) $timestamp < 1000000000) {
            return (string) $timestamp;
        }

        $date = is_numeric($timestamp)
            ? date_create('@' . ((int) $timestamp > 10000000000 ? (int) ($timestamp / 1000) : (int) $timestamp))
            : date_create((string) $timestamp);

        return $date ? $date->format('M j, Y, g:i A') : 'N/A';
    }

    private function historySortValue(string $id, array $record): int
    {
        $timestamp = $record['timestamp'] ?? $record['created_at'] ?? null;
        $time = $this->timestampToUnix($timestamp);

        return $time > 0 ? $time : $this->firebasePushIdToSortValue($id);
    }

    private function timestampToUnix(mixed $timestamp): int
    {
        if (! $timestamp) {
            return 0;
        }

        if (is_numeric($timestamp)) {
            $value = (int) $timestamp;

            if ($value < 1000000000) {
                return 0;
            }

            return $value > 10000000000 ? (int) ($value / 1000) : $value;
        }

        if (is_string($timestamp)) {
            return strtotime($timestamp) ?: 0;
        }

        return 0;
    }

    private function firebasePushIdToSortValue(string $id): int
    {
        $alphabet = '-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';

        if (strlen($id) < 8) {
            return 0;
        }

        $value = 0;

        foreach (str_split(substr($id, 0, 8)) as $char) {
            $index = strpos($alphabet, $char);

            if ($index === false) {
                return 0;
            }

            $value = ($value * 64) + $index;
        }

        return (int) floor($value / 1000);
    }

    private function dateKey(mixed $timestamp): ?string
    {
        if (! $timestamp) {
            return null;
        }

        if (is_numeric($timestamp) && (int) $timestamp < 1000000000) {
            return null;
        }

        $date = is_numeric($timestamp)
            ? date_create('@' . ((int) $timestamp > 10000000000 ? (int) ($timestamp / 1000) : (int) $timestamp))
            : date_create((string) $timestamp);

        return $date ? $date->format('Y-m-d') : null;
    }

}
