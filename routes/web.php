<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

Route::get('/test-base64-image', function () {
    // Hardcoded Base64 image string
    $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAZABkAA';

    // Decode Base64 data and process
    if (preg_match('/^data:image\/(?<type>[a-zA-Z]+);base64,(?<data>.*)$/', $base64Image, $matches)) {
        $type = $matches['type']; // image type (e.g., png)
        $data = $matches['data']; // Base64 data
        $extension = $type; // image type as file extension

        // Set up file storage path
        $folderPath = 'public/test_images';
        if (!Storage::disk('local')->exists($folderPath)) {
            Storage::disk('local')->makeDirectory($folderPath);
        }

        // Generate a unique filename
        $filename = uniqid() . '.' . $extension;
        $filePath = $folderPath . '/' . $filename;

        // Decode Base64 data and store the file
        $fileData = base64_decode($data);
        if ($fileData === false) {
            return response()->json(['error' => 'Base64 decode failed'], 400);
        }
        Storage::disk('local')->put($filePath, $fileData);

        return response()->json(['file_path' => $filePath], 200);
    } else {
        return response()->json(['error' => 'Invalid Base64 format'], 400);
    }
});
Route::get('/', function () {
    return view('welcome');  // Or your custom view
});

