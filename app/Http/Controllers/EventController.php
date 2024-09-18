<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;
use App\Models\Event;
use Illuminate\Http\Request;
use Validator;

class EventController extends Controller
{
//   public function index()
// {
//     $events = Event::orderBy('event_date', 'desc')->get();

//     if ($events->isEmpty()) {
//         return response()->json(['error' => 'No events found'], 404);
//     }

//     return response()->json(['events' => $events], 200);
// }
public function index(Request $request)
{
    $perPage = $request->input('per_page', 3); // Set the default items per page to 4
    $events = Event::orderBy('event_date', 'desc')->paginate($perPage); // Use pagination

    if ($events->isEmpty()) {
        return response()->json(['error' => 'No events found'], 404);
    }

    // Prepare the paginated response with pagination data
    return response()->json([
        'events' => $events->items(), // Get the paginated events
        'pagination' => [
            'total' => $events->total(),
            'per_page' => $events->perPage(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'from' => $events->firstItem(),
            'to' => $events->lastItem(),
        ]
    ], 200);
}


    public function show($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        return response()->json(['event' => $event], 200);
    }

public function store(Request $request)
{
    // Validate the incoming request data
    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'event_date' => 'required|date',
        'status' => 'required|in:start,soon,complete',
    ]);

    // Normalize the incoming title (remove spaces and convert to lowercase)
    $normalizedTitle = strtolower(preg_replace('/\s+/', '', $request->input('title')));

    // Check for uniqueness of the title in a case-insensitive and space-insensitive manner
    $titleExists = DB::table('events')
        ->whereRaw('LOWER(REPLACE(title, " ", "")) = ?', [$normalizedTitle])
        ->exists();

    if ($titleExists) {
        return response()->json(['message' => 'The title has already been taken.'], 422);
    }

    // Create a new event using the validated data
    $event = Event::create($validatedData);

    // Return a JSON response with the created event data
    return response()->json(['event' => $event], 201);
}


    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

         $validatedData = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'event_date' => 'date',
            'status' => 'in:start,soon,complete',
        ]);

        $event->update($request->all());

        return response()->json(['event' => $event], 200);
    }

    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully'], 200);
    }
}
