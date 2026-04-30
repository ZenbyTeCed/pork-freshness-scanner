<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;

class ResultController extends Controller
{
    protected $database;
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;

        $factory = (new Factory)->withServiceAccount(base_path('firebase-credentials.json.json'));
        $this->database = $factory->createDatabase();
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
            ->json([
                'grade' => $latestScan['grade'] ?? null,
                'confidence' => $latestScan['confidence'] ?? null,
                'mq135' => $latestScan['mq135'] ?? null,
                'temperature' => $latestScan['temperature'] ?? null,
                'humidity' => $latestScan['humidity'] ?? null,
                'image_url' => $latestScan['image_url'] ?? null,
                'created_at' => $latestScan['created_at'] ?? null,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function store(Request $request)
    {
        $request->validate([
            'scan_id' => 'required|string',
            'grade' => 'required|string',
            'confidence' => 'required|numeric',
            'details' => 'required|array',
        ]);

        $user = Auth::user();
        $scanId = $request->scan_id;

        // Verify scan belongs to user
        $scan = $this->database->getReference('scans/' . $scanId)->getValue();
        if (!$scan || $scan['user_id'] !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Store result
        $this->database->getReference('results/' . $scanId)->set([
            'grade' => $request->grade,
            'confidence' => $request->confidence,
            'details' => $request->details,
            'created_at' => now()->toISOString(),
        ]);

        return response()->json(['message' => 'Result stored successfully']);
    }

    public function show($scanId)
    {
        $user = Auth::user();
        $scan = $this->database->getReference('scans/' . $scanId)->getValue();

        if (!$scan || $scan['user_id'] !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 404);
        }

        $result = $this->database->getReference('results/' . $scanId)->getValue();

        return response()->json($result);
    }
}
