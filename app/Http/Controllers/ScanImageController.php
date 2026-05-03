<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ScanImageController extends Controller
{
    public function show(string $filename): BinaryFileResponse
    {
        $safeFilename = basename($filename);
        $path = storage_path("app/public/scans/{$safeFilename}");

        if (File::exists($path)) {
            return response()->file($path);
        }

        return response()->file(public_path('images/Porky Logo.png'));
    }
}
