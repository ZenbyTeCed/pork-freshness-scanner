<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class ResultController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(base_path('firebase-credentials.json.json'));
        $this->database = $factory->createDatabase();
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