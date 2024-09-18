<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Design;
use App\Models\Event;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class DesignController extends Controller
{


public function updateLogo(Request $request, $id)
{
    \Log::info('Logo value being saved: ' . $request->input('logo'));

    // Validate the incoming request
    $request->validate([
        'logo' => 'required|in:footer_only,header_footer', // Validate logo text input
    ]);

    // Find the event design by ID
    $eventDesign = Design::findOrFail($id);

    // Update the logo field
    $eventDesign->Logo = $request->input('logo');
    
    // Save the changes
    $eventDesign->save();

    // Return success response
    return response()->json([
        'message' => 'Logo updated successfully',
        'event_design' => $eventDesign,
    ], 200);
}

    
    // public function __construct()
    // {
    //     $this->middleware('jwt.verify', ['except' => ['index', 'show']]);
    // }


    // public function index(Request $request)
    // {
    //     $perPage = $request->input('per_page', 4); // Get per_page value from request or default to 10
    //     $designs = Design::with('event')->paginate($perPage);
    
    //     // Array to store the designs along with their event details and images
    //     $designData = [];
    
    //     foreach ($designs as $design) {
    //         // Get the event associated with the design
    //         $event = $design->event;
    
    //         // Check if the event was found
    //         if (!$event) {
    //             continue; // Skip designs without an associated event
    //         }
    
    //         // Get the event title
    //         $eventTitle = $event->title;
    
    //         // Define the directory path based on the event title
    //         $directoryPath = 'public/' . str_replace(' ', '-', strtolower($eventTitle));
    
    //         // Initialize an array to store Base64-encoded images for the current design
    //         $imageData = [];
    
    //         // Check if the directory exists
    //         if (Storage::disk('local')->exists($directoryPath)) {
    //             // Get all files in the directory
    //             $files = Storage::disk('local')->files($directoryPath);
    
    //             // Iterate through each file and convert to Base64
    //             foreach ($files as $filePath) {
    //                 // Read the image content from local storage
    //                 $imageContent = Storage::disk('local')->get($filePath);
    
    //                 // Convert the image content to Base64 format
    //                 $base64Image = base64_encode($imageContent);
    
    //                 // Add the Base64-encoded image to the image data array
    //                 $imageData[] = $base64Image;
    //             }
    //         } else {
    //             Log::info('Directory not found: ' . $directoryPath);
    //         }
    
    //         // Add the design and its images to the design data array
    //         $designData[] = [
    //             'design_id' => $design->id,
    //             'event_id' => $event->id,
    //             'title' => $eventTitle,
    //             'description' => $event->description,
    //             'images' => $imageData
    //         ];
    //     }
    
    //     // Return the retrieved design data as JSON with pagination metadata
    //     return response()->json([
    //         'Design' => $designData,
    //         'pagination' => [
    //             'total' => $designs->total(),
    //             'per_page' => $designs->perPage(),
    //             'current_page' => $designs->currentPage(),
    //             'last_page' => $designs->lastPage(),
    //             'from' => $designs->firstItem(),
    //             'to' => $designs->lastItem(),
    //         ]
    //     ], 200);
    // }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 3); 
        $designs = Design::with('event')->paginate($perPage);

        $designData = [];

        foreach ($designs as $design) {
            $event = $design->event;

            if (!$event) {
                continue; 
            }

            $designData[] = [
                'design_id' => $design->id,
                'event_id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
            ];
        }

        return response()->json([
            'Design' => $designData,
            'pagination' => [
                'total' => $designs->total(),
                'per_page' => $designs->perPage(),
                'current_page' => $designs->currentPage(),
                'last_page' => $designs->lastPage(),
                'from' => $designs->firstItem(),
                'to' => $designs->lastItem(),
            ]
        ], 200);
    }


    // public function getDesignImages($designId)
    // {
        
    //     $design = Design::find($designId);

    //     if (!$design) {
    //         return response()->json(['message' => 'Design not found'], 404);
    //     }

    //     $event = $design->event;

    //     if (!$event) {
    //         return response()->json(['message' => 'Event not found'], 404);
    //     }

    //     $eventTitle = $event->title;

    //     $directoryPath = 'public/' . str_replace(' ', '-', strtolower($eventTitle));

    //     $imageData = [];

    //     if (Storage::disk('local')->exists($directoryPath)) {
    //         $files = Storage::disk('local')->files($directoryPath);

    //         foreach ($files as $filePath) {
    //             $imageContent = Storage::disk('local')->get($filePath);

    //             $base64Image = base64_encode($imageContent);

    //             $imageData[] = $base64Image;
    //         }
    //     } else {
    //         Log::info('Directory not found: ' . $directoryPath);
    //     }

    //     return response()->json(['images' => $imageData], 200);
    // }
public function getDesignImages($designId)
{
    $design = Design::findorfail($designId);

    if (!$design) {
        return response()->json(['message' => 'Design not found'], 404);
    }

    $event = $design->event;

    if (!$event) {
        return response()->json(['message' => 'Event not found'], 404);
    }

    $eventTitle = $event->title;
    $folderName = str_replace(' ', '-', strtolower($eventTitle));
    $directoryPath = 'public/' . $folderName; // Main event directory
    $headerFolderPath = $directoryPath . '/header'; // Header folder path

    $imageData = [];
    $headerImage = null; // Initialize header image variable

    // Retrieve images from the event's main directory
    if (Storage::disk('local')->exists($directoryPath)) {
        $files = Storage::disk('local')->files($directoryPath);

        foreach ($files as $filePath) {
            $imageContent = Storage::disk('local')->get($filePath);
            $base64Image = base64_encode($imageContent);
            $imageData[] = $base64Image;
        }
    } else {
        Log::info('Directory not found: ' . $directoryPath);
    }

    // Check if the header image exists and add it to the response
    if ($design->header_image) {
        $headerImagePath = 'public/' . $design->header_image;

        if (Storage::disk('local')->exists($headerImagePath)) {
            $headerImageContent = Storage::disk('local')->get($headerImagePath);
            $headerImage = base64_encode($headerImageContent); // Encode header image in Base64
        } else {
            Log::info('Header image not found: ' . $headerImagePath);
        }
    }

    // Return images and header image separately
    return response()->json([
        'images' => $imageData,
        'header_image' => $headerImage, 
    ], 200);
}


    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    // Validate the incoming request
    $validatedData = $request->validate([
        'event_id' => 'required|exists:events,id',
        'images' => 'required|array',
        'images.*' => 'required|string', 
    ]);

    // Fetch the event to get the title
    $event = Event::findOrFail($validatedData['event_id']);
    $eventTitle = $event->title;

    // Create a folder for storing images
    $folderName = Str::slug($eventTitle);
    $folderPath = 'public/' . $folderName;

    if (!Storage::disk('local')->exists($folderPath)) {
        Storage::disk('local')->makeDirectory($folderPath);
    }

    $storedImagePaths = [];
    foreach ($validatedData['images'] as $base64Image) {
        $filename = uniqid() . '_' . time() . '.png'; 
        $decodedImage = base64_decode($base64Image);
        Storage::disk('local')->put($folderPath . '/' . $filename, $decodedImage);
        $storedImagePaths[] = $folderName . '/' . $filename; 
    }

    // Create and save the new design record
    $eventDesign = new Design();
    $eventDesign->event_id = $validatedData['event_id'];
    $eventDesign->images = json_encode($storedImagePaths);
    $eventDesign->save();

    return response()->json($eventDesign, 201);
}



public function updatedelete(Request $request, $id)
{
    \Log::info('Update method called with data:', $request->all());

    // Validate the incoming request
    $validatedData = $request->validate([
        'event_id' => 'required|exists:events,id',
        'images' => 'nullable|array',
        'images.*' => 'nullable|string',
        'header_image' => 'nullable|string',
        'delete_images' => 'nullable|array',
        'delete_images.*' => 'nullable|string',
    ]);

    // Fetch the existing event design
    $eventDesign = Design::findOrFail($id);
    $existingImages = json_decode($eventDesign->images, true) ?: [];

    // Store folder details for images
    $event = Event::findOrFail($validatedData['event_id']);
    $folderName = Str::slug($event->title);
    $folderPath = 'public/';

    // Create folder if it doesn't exist
    if (!Storage::disk('local')->exists($folderPath)) {
        Storage::disk('local')->makeDirectory($folderPath);
    }

    // Process new images (Base64)
    $newImagePaths = [];
    if (isset($validatedData['images'])) {
        foreach ($validatedData['images'] as $base64Image) {
            if (str_starts_with($base64Image, 'data:image/')) { // Check if it's a valid Base64 image
                $filename = uniqid() . '_' . time() . '.png';
                $decodedImage = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));
                
                if ($decodedImage === false) {
                    \Log::error("Base64 decoding failed for image: $base64Image");
                    continue;
                }

                Storage::disk('local')->put($folderPath . '/' . $filename, $decodedImage);
                $newImagePaths[] = $folderName . '/' . $filename; // Store the path for new images
   \Log::info('New image path stored: ' . $folderName . '/' . $filename); // Log the new image path

            }
        }
    }

    // Remove images that are marked for deletion
    if (isset($validatedData['delete_images'])) {
        foreach ($validatedData['delete_images'] as $base64Image) {
            // Decode base64 image to find the actual image content
            $cleanBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            $decodedImage = base64_decode($cleanBase64);
            
            if ($decodedImage === false) {
                \Log::warning("Base64 decoding failed for image marked for deletion: $base64Image");
                continue;
            }

            // Find and delete the matching image
            foreach ($existingImages as $existingImage) {
                $fullPath = $folderPath . '/' . $existingImage;
                
                if (Storage::disk('local')->exists($fullPath)) {
                    $storedImageContent = Storage::disk('local')->get($fullPath);
                    
                    if ($decodedImage === $storedImageContent) {
                        Storage::disk('local')->delete($fullPath);
                        \Log::info("Deleted image: $fullPath");
                        // Remove the image path from the existing images array
                        $existingImages = array_filter($existingImages, function($path) use ($existingImage) {
                            return $path !== $existingImage;
                        });
                        break; // Exit loop after deleting the image
                    }
                } else {
                     \Log::warning("folderPath: " . $folderPath);
\Log::warning("Attempted to delete non-existent image: " . $fullPath);
 \Log::warning("existingImagePath: " . $existingImage);

                }
            }
        }
    }

    // Merge new images with existing images
    $updatedImages = array_merge($existingImages, $newImagePaths);

    // Update event design images
    $eventDesign->images = json_encode($updatedImages);

    // Handle header image
    if (!empty($validatedData['header_image'])) {
        $headerFolderPath = $folderPath . '/header';
        if (!Storage::disk('local')->exists($headerFolderPath)) {
            Storage::disk('local')->makeDirectory($headerFolderPath);
        }

        $headerImageName = uniqid() . '_header_' . time() . '.png';
        $decodedHeaderImage = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $validatedData['header_image']));
        
        if ($decodedHeaderImage === false) {
            \Log::error("Base64 decoding failed for header image: {$validatedData['header_image']}");
        } else {
            Storage::disk('local')->put($headerFolderPath . '/' . $headerImageName, $decodedHeaderImage);
            $eventDesign->header_image = $folderName . '/header/' . $headerImageName;
        }
    }

    // Save updated event design
    $eventDesign->save();

    return response()->json($eventDesign);
}

// public function update(Request $request, $id)
// {
//     // Log incoming request data for debugging
//     \Log::info('Incoming request data:', $request->all());

//     // Validate incoming request data
//     $validatedData = $request->validate([
//         'event_id' => 'required|exists:events,id',
//         'images' => 'nullable|array',
//         'images.*' => 'nullable|string',
//         'delete_images' => 'nullable|array',
//     ]);

//     // Fetch the event design and event
//     $eventDesign = Design::findOrFail($id);
//     $event = Event::findOrFail($validatedData['event_id']);

//     // Create folder path based on event title
//     $folderName = Str::slug($event->title);
//     $folderPath = 'public/' . $folderName;

//     // Create directory if it doesn't exist
//     if (!Storage::disk('local')->exists($folderPath)) {
//         Storage::disk('local')->makeDirectory($folderPath);
//     }

//     // Fetch existing images
//     $existingImages = json_decode($eventDesign->images, true) ?? [];
//  \Log::info("decoded image: $existingImages");
//     // Remove images that are to be deleted
//   // Remove images that are to be deleted
// // Remove images that are to be deleted
// if (isset($validatedData['delete_images'])) {
//     foreach ($validatedData['delete_images'] as $imageFilename) {
//         $fullPath = $folderPath . '/' . $imageFilename;

//         \Log::info("Attempting to delete image: $fullPath");

//         if (Storage::disk('local')->exists($fullPath)) {
//             Storage::disk('local')->delete($fullPath);
//             \Log::info("Deleted image: $fullPath");
//         } else {
//             \Log::warning("Attempted to delete non-existent image: $fullPath");
//         }
//     }
// }


//     // Initialize arrays for new images
//     $newImagePaths = [];

//     // Check if 'images' key exists in validated data
//     if (isset($validatedData['images'])) {
//         foreach ($validatedData['images'] as $base64Image) {
//             // Clean base64 format
//             $cleanBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
//             // Generate a unique filename for each image
//             $filename = uniqid() . '_' . time() . '.png';

//             // Decode base64 image and store it in the folder
//             $decodedImage = base64_decode($cleanBase64);
//             if ($decodedImage === false) {
//                 \Log::error("Failed to decode base64 image for event design ID: $id");
//                 continue; // Skip if decoding fails
//             }

//             // Store the decoded image in the folder
//             Storage::disk('local')->put($folderPath . '/' . $filename, $decodedImage);
//             $newImagePaths[] = $folderName . '/' . $filename; // Store the complete path
//         }
//     }

//     // Merge existing and new images
//     $mergedImages = array_merge($existingImages, $newImagePaths);

//     // Update the event design in the database
//     $eventDesign->event_id = $validatedData['event_id'];
//     $eventDesign->images = json_encode(array_values($mergedImages)); // Store merged images

//     // Log the updated images for debugging
//     \Log::info('Updated images for event design:', [
//         'event_design_id' => $id,
//         'images' => array_values($mergedImages),
//     ]);

//     // Save the event design
//     try {
//         $eventDesign->save();
//     } catch (\Exception $e) {
//         \Log::error('Failed to save event design', [
//             'event_design_id' => $id,
//             'error' => $e->getMessage(),
//         ]);
//         return response()->json(['message' => 'Error updating event design'], 500);
//     }

//     // Return the updated event design in the response
//     return response()->json([
//         'event_design' => $eventDesign,
//         'message' => 'Event design updated successfully',
//     ]);
// }



// public function update(Request $request, $id)
// {
//     \Log::info('Update method called with data:', $request->all());

//     // Validate the incoming request
//     $validatedData = $request->validate([
//         'event_id' => 'required|exists:events,id',
//         'images' => 'nullable|array',
//         'images.*' => 'nullable|string',
//         'header_image' => 'nullable|string',
//         'delete_images' => 'nullable|array',
//         'delete_images.*' => 'nullable|string',
//     ]);

//     // Fetch the existing event design
//     $eventDesign = Design::findOrFail($id);
//     $existingImages = json_decode($eventDesign->images, true) ?: [];

//     // Store folder details for images
//     $event = Event::findOrFail($validatedData['event_id']);
//     $folderName = Str::slug($event->title);
//     $folderPath = 'public/' . $folderName;

//     // Create folder if it doesn't exist
//     if (!Storage::disk('local')->exists($folderPath)) {
//         Storage::disk('local')->makeDirectory($folderPath);
//     }

//     // Process new images (Base64)
//     $newImagePaths = [];
//     if (isset($validatedData['images'])) {
//         foreach ($validatedData['images'] as $base64Image) {
//             if (str_starts_with($base64Image, 'data:image/')) { // Check if it's a valid Base64 image
//                 $filename = uniqid() . '_' . time() . '.png';
//                 $decodedImage = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));
                
//                 if ($decodedImage === false) {
//                     \Log::error("Base64 decoding failed for image: $base64Image");
//                     continue;
//                 }

//                 Storage::disk('local')->put($folderPath . '/' . $filename, $decodedImage);
//                 $newImagePaths[] = $folderName . '/' . $filename; // Store the path for new images
//             }
//         }
//     }

//     // Remove images that are marked for deletion
//     if (isset($validatedData['delete_images'])) {
//         foreach ($validatedData['delete_images'] as $base64Image) {
//             // Decode base64 image to find the actual image content
//             $cleanBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
//             $decodedImage = base64_decode($cleanBase64);
            
//             if ($decodedImage === false) {
//                 \Log::warning("Base64 decoding failed for image marked for deletion: $base64Image");
//                 continue;
//             }

//             // Find and delete the matching image
//             foreach ($existingImages as $existingImage) {
//                 $fullPath = $folderPath . '/' . $existingImage;
                
//                 if (Storage::disk('local')->exists($fullPath)) {
//                     $storedImageContent = Storage::disk('local')->get($fullPath);
                    
//                     if ($decodedImage === $storedImageContent) {
//                         Storage::disk('local')->delete($fullPath);
//                         \Log::info("Deleted image: $fullPath");
//                         // Remove the image path from the existing images array
//                         $existingImages = array_filter($existingImages, function($path) use ($existingImage) {
//                             return $path !== $existingImage;
//                         });
//                         break; // Exit loop after deleting the image
//                     }
//                 } else {
//                     \Log::warning("Attempted to delete non-existent image: $fullPath");
//                 }
//             }
//         }
//     }

//     // Merge new images with existing images
//     $updatedImages = array_merge($existingImages, $newImagePaths);

//     // Update event design images
//     $eventDesign->images = json_encode($updatedImages);

//     // Handle header image
//     if (!empty($validatedData['header_image'])) {
//         $headerFolderPath = $folderPath . '/header';
//         if (!Storage::disk('local')->exists($headerFolderPath)) {
//             Storage::disk('local')->makeDirectory($headerFolderPath);
//         }

//         $headerImageName = uniqid() . '_header_' . time() . '.png';
//         $decodedHeaderImage = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $validatedData['header_image']));
        
//         if ($decodedHeaderImage === false) {
//             \Log::error("Base64 decoding failed for header image: {$validatedData['header_image']}");
//         } else {
//             Storage::disk('local')->put($headerFolderPath . '/' . $headerImageName, $decodedHeaderImage);
//             $eventDesign->header_image = $folderName . '/header/' . $headerImageName;
//         }
//     }

//     // Save updated event design
//     $eventDesign->save();

//     return response()->json($eventDesign);
// }


public function update2(Request $request, $id)
{
    \Log::info('Update method called with data:', $request->all());

    // Validate the incoming request
    $validatedData = $request->validate([
        'event_id' => 'required|exists:events,id',
        'images' => 'nullable|array',
        'images.*' => 'nullable|string',
        'header_image' => 'nullable|string',
        'delete_images' => 'nullable|array',
        'delete_images.*' => 'nullable|string',
    ]);

    // Fetch the existing event design
    $eventDesign = Design::findOrFail($id);
    $existingImages = json_decode($eventDesign->images, true) ?: [];

    // Store folder details for images
    $event = Event::findOrFail($validatedData['event_id']);
    $folderName = Str::slug($event->title);
    $folderPath = 'public/';

    // Create folder if it doesn't exist
    if (!Storage::disk('local')->exists($folderPath)) {
        Storage::disk('local')->makeDirectory($folderPath);
    }

    // Process new images (Base64)
    $newImagePaths = [];
    if (isset($validatedData['images'])) {
        foreach ($validatedData['images'] as $base64Image) {
            if (str_starts_with($base64Image, '/9j/')) { // Handle only new Base64 images
                $filename = uniqid() . '_' . time() . '.png';
                $decodedImage = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));

                if ($decodedImage === false) {
                    \Log::error("Base64 decoding failed for image: $base64Image");
                    continue;
                }

                // Store the new image
                Storage::disk('local')->put($folderPath . '/' . $filename, $decodedImage);
                $newImagePaths[] = $folderName . '/' . $filename;
            }
        }
    }

    // Remove images that are marked for deletion
    if (isset($validatedData['delete_images'])) {
        foreach ($validatedData['delete_images'] as $imagePath) {
            $fullPath = 'public/' . $imagePath;
            if (Storage::disk('local')->exists($fullPath)) {
                Storage::disk('local')->delete($fullPath);

                // Remove the image path from the existing images array
                $existingImages = array_filter($existingImages, function($path) use ($imagePath) {
                    return $path !== $imagePath;
                });
            } else {
                \Log::warning("Attempted to delete non-existent image: $fullPath");
            }
        }
    }

    // Merge new images with existing images, ensuring no duplicates
    $updatedImages = array_unique(array_merge($existingImages, $newImagePaths));

    // Update event design images
    $eventDesign->images = json_encode($updatedImages);

    // Handle header image
    if (!empty($validatedData['header_image'])) {
        $headerFolderPath = $folderPath . '/header';
        if (!Storage::disk('local')->exists($headerFolderPath)) {
            Storage::disk('local')->makeDirectory($headerFolderPath);
        }

        $headerImageName = uniqid() . '_header_' . time() . '.png';
        $decodedHeaderImage = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $validatedData['header_image']));
        
        if ($decodedHeaderImage === false) {
            \Log::error("Base64 decoding failed for header image: {$validatedData['header_image']}");
        } else {
            Storage::disk('local')->put($headerFolderPath . '/' . $headerImageName, $decodedHeaderImage);
            $eventDesign->header_image = $folderName . '/header/' . $headerImageName;
        }
    }

    // Save updated event design
    $eventDesign->save();

    return response()->json($eventDesign);
}


public function update(Request $request, $id)
{
    \Log::info('Update method called with data:', $request->all());

    // Validate incoming request data
    $validatedData = $request->validate([
        'event_id' => 'required|exists:events,id',
        'images' => 'nullable|array',
        'images.*' => 'nullable|string',
        'header_image' => 'nullable|string',
        'delete_images' => 'nullable|array',
        'delete_images.*' => 'nullable|string',
    ]);

    // Fetch the event design and event
    $eventDesign = Design::findOrFail($id);
    $event = Event::findOrFail($validatedData['event_id']);

    // Create folder path based on event title
    $folderName = Str::slug($event->title);
    $folderPath = 'public/' . $folderName;

    // Create directory if it doesn't exist
    if (!Storage::disk('local')->exists($folderPath)) {
        Storage::disk('local')->makeDirectory($folderPath);
    }

    // Fetch existing images
    $existingImages = json_decode($eventDesign->images, true) ?? [];
    \Log::info('Existing images for event design:', [
        'existing_images' => $existingImages,
    ]);

    // Remove images that are marked for deletion
    if (isset($validatedData['delete_images'])) {
        foreach ($validatedData['delete_images'] as $base64Image) {
            // Decode base64 image to find the actual image content
            $cleanBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            $decodedImage = base64_decode($cleanBase64);
            
            if ($decodedImage === false) {
                \Log::warning("Base64 decoding failed for image marked for deletion: $base64Image");
                continue;
            }

            // Find and delete the matching image
            foreach ($existingImages as $existingImage) {
                $fullPath = 'public' . '/' . $existingImage;
                
                if (Storage::disk('local')->exists($fullPath)) {
                    $storedImageContent = Storage::disk('local')->get($fullPath);
                    
                    if ($decodedImage === $storedImageContent) {
                        Storage::disk('local')->delete($fullPath);
                        \Log::info("Deleted image: $fullPath");
                        // Remove the image path from the existing images array
                        $existingImages = array_filter($existingImages, function($path) use ($existingImage) {
                            return $path !== $existingImage;
                        });
                        break; // Exit loop after deleting the image
                    }
                } else {
                    \Log::warning("Attempted to delete non-existent image: " . $fullPath);
                }
            }
        }
    }

    // Initialize arrays for new images
    $newImagePaths = [];

    // Check if 'images' key exists in validated data
    if (isset($validatedData['images'])) {
        foreach ($validatedData['images'] as $base64Image) {
            // Ensure base64 format is clean (remove prefix if exists)
            $cleanBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);

            // Generate a unique filename for each image
            $filename = uniqid() . '_' . time() . '.png';

            // Decode base64 image and store it in the folder
            $decodedImage = base64_decode($cleanBase64);
            if ($decodedImage === false) {
                \Log::error("Failed to decode base64 image for event design ID: $id");
                continue; // Skip if decoding fails
            }

            Storage::disk('local')->put($folderPath . '/' . $filename, $decodedImage);

            // Add the new image path to the array
            $newImagePaths[] = $folderName . '/' . $filename;
        }
    }

    // Ensure existingImages are strings and filter them
    $existingImages = array_filter($existingImages, 'is_string');

    // Merge existing and new images, only keeping those that are valid
    $mergedImages = array_merge($existingImages, $newImagePaths);

    // Update the event design in the database
    $eventDesign->event_id = $validatedData['event_id'];
    $eventDesign->images = json_encode(array_values($mergedImages)); // Store merged images

    // Handle header image
    if (!empty($validatedData['header_image'])) {
        $headerFolderPath = $folderPath . '/header';
        if (!Storage::disk('local')->exists($headerFolderPath)) {
            Storage::disk('local')->makeDirectory($headerFolderPath);
        }

        $headerImageName = uniqid() . '_header_' . time() . '.png';
        $decodedHeaderImage = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $validatedData['header_image']));
        
        if ($decodedHeaderImage === false) {
            \Log::error("Base64 decoding failed for header image: {$validatedData['header_image']}");
        } else {
            Storage::disk('local')->put($headerFolderPath . '/' . $headerImageName, $decodedHeaderImage);
            $eventDesign->header_image = $folderName . '/header/' . $headerImageName;
        }
    }

    // Log the updated images for debugging
    \Log::info('Updated images for event design:', [
        'event_design_id' => $id,
        'images' => array_values($mergedImages),
    ]);

    // Save the event design
    try {
        $eventDesign->save();
    } catch (\Exception $e) {
        \Log::error('Failed to save event design', [
            'event_design_id' => $id,
            'error' => $e->getMessage(),
        ]);
        return response()->json(['message' => 'Error updating event design'], 500);
    }

    // Return the updated event design in the response
    return response()->json([
        'event_design' => $eventDesign,
        'message' => 'Event design updated successfully',
    ]);
}






    
    /**
     * Display the specified resource.
     */
 public function show($id)
{
    // Find the event by ID
    $event = Event::find($id);
    
    if (!$event) {
        return response()->json(['message' => 'Event not found.'], 404);
    }
    
    $eventid = $event->id;
    
    // Find the design associated with the event
    $design = Design::where('event_id', $eventid)->first();

    
    if (!$design) {
        return response()->json(['message' => 'Design not found.'], 404);  
    }
    
    // Retrieve the logo from the design
    $eventlogo = $design->Logo;

    // Prepare directory paths
    $eventTitle = $event->title;
    $folderName = str_replace(' ', '-', strtolower($eventTitle));
    $directoryPath = 'public/' . $folderName; // Main event directory
    $headerFolderPath = $directoryPath . '/header'; // Header folder path

    // Check if the main event directory exists
    if (!Storage::disk('local')->exists($directoryPath)) {
        return response()->json(['status' => 'failed', 'message' => 'No Designs available for this Event.'], 200);
    }

    // Get all files in the main event directory
    $files = Storage::disk('local')->files($directoryPath);
    $imageData = [];

    // Encode all files as Base64
    foreach ($files as $filePath) {
        $imageContent = Storage::disk('local')->get($filePath);
        $base64Image = base64_encode($imageContent);
        $imageData[] = $base64Image;
    }

    // Retrieve the header image from the header folder
    $headerImage = null; // Initialize header image variable
    if (Storage::disk('local')->exists($headerFolderPath)) {
        // Assuming only one header image in the folder
        $headerFiles = Storage::disk('local')->files($headerFolderPath);

        if (!empty($headerFiles)) {
            $headerImageContent = Storage::disk('local')->get($headerFiles[0]);
            $headerImage = base64_encode($headerImageContent);
        }
    }

    // Return response with images and header image
    return response()->json([
        'event_id' => $id,
        'title' => $eventTitle,
        'images' => $imageData,
        'header_image' => $headerImage,
        'logo' => $eventlogo,
    ], 200);
}


    /**
     * Update the specified resource in storage.
     */
   
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $eventDesign = Design::find($id);

        if (!$eventDesign) {
            return response()->json(['message' => 'Event design not found.'], 404);
        }

        $event = Event::find($eventDesign->event_id);

        $eventTitle = $event->title;

        $directoryPath = 'public/' . str_replace(' ', '-', strtolower($eventTitle));

        if (Storage::disk('local')->exists($directoryPath)) {
            Storage::disk('local')->deleteDirectory($directoryPath);
        }

        $eventDesign->delete();

        return response()->json(['message' => 'Event design and associated images deleted successfully.'], 200);
    }

    public function image(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storeAs('public/images', $file->getClientOriginalName());
            
            return response()->json(['filePath' => Storage::url($path)], 200);
        }

        return response()->json(['error' => 'File not uploaded'], 400);
    }

   
}

