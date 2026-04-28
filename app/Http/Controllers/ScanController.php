<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class ScanController extends Controller
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $image = $request->file('image');

        // Store image temporarily or in storage
        $imagePath = $image->store('scans', 'public');

        // Placeholder for Edge Impulse integration
        // Here you would send the image to Edge Impulse API and get results
        // For now, mock results
        $mlResult = [
            'grade' => 'A',
            'confidence' => 0.95,
            'details' => [
                'color' => 'Fresh',
                'surface' => 'Clean',
                'texture' => 'Firm'
            ]
        ];

        // Store scan in Firebase
        $scanRef = $this->database->getReference('scans')->push([
            'user_id' => $user->id,
            'image_path' => $imagePath,
            'created_at' => now()->toISOString(),
        ]);

        $scanId = $scanRef->getKey();

        // Store result in Firebase
        $this->database->getReference('results/' . $scanId)->set([
            'grade' => $mlResult['grade'],
            'confidence' => $mlResult['confidence'],
            'details' => $mlResult['details'],
            'created_at' => now()->toISOString(),
        ]);

        return response()->json([
            'scan_id' => $scanId,
            'result' => $mlResult,
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $scans = $this->database->getReference('scans')
            ->orderByChild('user_id')
            ->equalTo($user->id)
            ->getValue();

        return response()->json($scans);
    }

    public function show($id)
    {
        $user = Auth::user();
        $scan = $this->database->getReference('scans/' . $id)->getValue();

        if (!$scan || $scan['user_id'] !== $user->id) {
            return response()->json(['error' => 'Scan not found'], 404);
        }

        $result = $this->database->getReference('results/' . $id)->getValue();

        return response()->json([
            'scan' => $scan,
            'result' => $result,
        ]);
    }
}