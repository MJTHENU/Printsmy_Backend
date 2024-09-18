<?php
  
namespace App\Http\Controllers\Auth;
  
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
// use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Log;
  
  
class AuthController extends Controller
{

    // public function index()
    // {
        
    //     $user = User::all();
        
    //     return response(['users' => $user]);
    // }
public function index(Request $request)
{
    $perPage = $request->input('per_page', 5); // Default to 10 users per page
    $users = User::paginate($perPage); // Paginate the user data

    // Directly map the user model to the response
    $userData = $users->map(function ($user) {
        return $user->toArray(); // Convert the user model to an array
    });

    return response()->json([
        'users' => $userData,
        'pagination' => [
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'from' => $users->firstItem(),
            'to' => $users->lastItem(),
        ]
    ], 200);
}

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
  public function register(Request $request)
    {
        try {
            // Validation with custom regex for Bill_no
            $validated = $request->validate([
                'Bill_no' => [
                    'required',
                    'string',
                    'regex:/^[A-Za-z]{1}\d{4}$/',
                    'max:5',
                    'unique:users,Bill_no' 
                ],
                'contact_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email',
                 'refemail'=>'required',
                'password' => 'required|string|confirmed|max:4',
                'phone' => 'required|string|regex:/^[0-9]{10}$/|unique:users,phone|max:255',
                'whatsapp' => 'required|string|regex:/^[0-9]{10}$/|max:255',
                'company_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255',
                'gst' => 'nullable|string|max:255',
                'address1' => 'nullable|string|max:255',
                'address2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'pincode' => 'required|string|regex:/^[0-9]{6}$/|max:255',
                'state' => 'required|string|max:255',
                'company_logo' => 'nullable|string',
                'company_logo_header' => 'nullable|string',
                'status' => 'required|string|max:255',
                'role' => 'required|string|in:admin,user',
                'join_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:' . date('Y-m-d'),
                    'before_or_equal:' . date('Y-m-d', strtotime('+1 year'))
                ],
            ], [
                'Bill_no.regex' => 'The Bill number must contain exactly one alphabet followed by four digits.',
                'contact_name.regex' => 'The contact name must contain only alphabets.',
                'company_name.regex' => 'The company name must contain only alphabets.',
                'phone.regex' => 'The phone number must be exactly 10 digits.',
                'whatsapp.regex' => 'The WhatsApp number must be exactly 10 digits.',
                'pincode.regex' => 'The pincode must be exactly 6 digits.',
            ]);
    
            // Create the user
            $user = new User([
                'Bill_no' => $validated['Bill_no'],
                'refemail' => $validated['refemail'],
                'contact_name' => $validated['contact_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'whatsapp' => $validated['whatsapp'],
                'company_name' => $validated['company_name'],
                'gst' => $validated['gst'],
                'address1' => $validated['address1'],
                'address2' => $validated['address2'],
                'city' => $validated['city'],
                'pincode' => $validated['pincode'],
                'state' => $validated['state'],
                'company_logo' => $validated['company_logo'],
                 'company_logo_header' => $validated['company_logo_header'],
                'status' => $validated['status'],
                'role' => $validated['role'],
                'join_date' => $validated['join_date'],
            ]);
    
            $user->save();
    
            $token = auth()->tokenById($user->id);
    
            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
                'token' => $token
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation Failed', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            return response()->json(['error' => 'User creation failed. Please try again.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

public function update(Request $request, $id)
{
    try {
        // Validate the incoming request data
        $validated = $request->validate([
            'Bill_no' => 'required|string|min:4|unique:users,Bill_no,' . $id,
            'contact_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:4',
            'phone' => 'required|string|regex:/^[0-9]{10}$/|unique:users,phone,' . $id,
            'whatsapp' => 'required|string|regex:/^[0-9]{10}$/|unique:users,whatsapp,' . $id,
            'company_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255',
            'gst' => 'nullable|string|max:255',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'required|string|regex:/^[0-9]{6}$/|max:255',
            'state' => 'required|string|max:255',
            'company_logo' => 'nullable|string',
            'company_logo_header' => 'nullable|string',
            'status' => 'required|string|max:255',
            'role' => 'required|string|in:admin,user',
            'join_date' => [
                'nullable',
                'date',
                'after_or_equal:' . date('Y-m-d'),
                'before_or_equal:' . date('Y-m-d', strtotime('+1 year'))
            ],
        ], [
            'Bill_no.unique' => 'This Bill number already exists. Try another number.',
            'Bill_no.regex' => 'The Bill number must contain both letters and numbers.',
            'contact_name.regex' => 'The contact name must contain only alphabets.',
            'company_name.regex' => 'The company name must contain only alphabets.',
            'phone.regex' => 'The phone number must be exactly 10 digits.',
            'whatsapp.regex' => 'The WhatsApp number must be exactly 10 digits.',
            'pincode.regex' => 'The pincode must be exactly 6 digits.',
             
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update the user with validated data
        $user->fill($validated);

        // If a password is provided, hash it before saving
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        // Save the updated user to the database
        $user->save();

        // Return a success response
        return response()->json(['message' => 'User updated successfully'], 200);
    } catch (ValidationException $e) {
        return response()->json(['message' => 'Failed', 'errors' => $e->errors()], 422);
    } catch (QueryException $e) {
        return response()->json(['error' => 'User update failed. Please try again.'], 500);
    } catch (\Exception $e) {
        \Log::error('User update error: ', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
    }
}


    public function updatePassword(Request $request, $id)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'password' => 'required|string|confirmed|max:4',
            ]);

            $user = User::findOrFail($id);

            // Update user password
            $user->password = Hash::make($validated['password']);

            $user->save();

            return response()->json([
                'message' => 'Password updated successfully',
                'user' => $user,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Failed', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Password update failed. Please try again.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function updateStatus($id, Request $request)
    {
        // Validate the request
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        try {
            // Find the user by ID
            $user = User::findOrFail($id);

            // Update the status
            $user->status = $request->input('status');
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id) {
        $user = User::find($id);

        if(!$user) {
            return response()->json(['error' => 'User not found']);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
  
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|string',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         return response()->json(['message' => 'Email does not exist'], 404);
    //     }

    //     if (!Hash::check($request->password, $user->password)) {
    //         return response()->json(['message' => 'Incorrect password'], 404);
    //     }

    //     if (!$token = auth()->attempt($request->only('email', 'password'))) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }

    //     return response()->json([
    //         'name' => $user->contact_name,
    //         'role' => $user->role,
    //         'id' => $user->id,
    //         'token' => $token,
    //     ]);
    // }

//     public function login(Request $request)
// {
//     $request->validate([
//         'login' => 'required',
//         'password' => 'required|string',
//     ]);

//     $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

//     $user = User::where($loginField, $request->login)->first();

//     if (!$user) {
//         // If the email or mobile does not exist in the database
//         return response()->json(['message' => 'User does not exist'], 404);
//     }

//     if (!Hash::check($request->password, $user->password)) {
//         // If the password is incorrect
//         return response()->json(['message' => 'Incorrect password'], 404);
//     }

//     // Generate token for the user
//     if (!$token = auth()->attempt([$loginField => $request->login, 'password' => $request->password])) {
//         return response()->json(['message' => 'Unauthorized'], 401);
//     }

//     return response()->json([
//         'name' => $user->first_name . " " . $user->last_name,
//         'role' => $user->role,
//         'id' => $user->id,
//         'token' => $token,
//     ]);
// }



public function login(Request $request)
{
    $request->validate([
        'login' => 'required|string|max:225',
        'password' => 'required|string',
    ]);

    $loginInput = $request->input('login');
    $loginType = null;

    // Check if the login input is an email
    if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
        $loginType = 'email';
    } 
    // Check if the login input is numeric (for phone)
    elseif (is_numeric($loginInput)) {
        $loginType = 'phone';
    } 
    // Check if the login input is a Bill Number (assuming it's alphanumeric)
    elseif (preg_match('/^[A-Za-z]\d{4}$/', $loginInput)) {
        $loginType = 'Bill_no'; // Recognize it as a Bill Number
    } 
    // Check if the login input is a WhatsApp number
    elseif (str_starts_with($loginInput, 'whatsapp:')) {
        $loginType = 'whatsapp';
        $loginInput = str_replace('whatsapp:', '', $loginInput);
    }

    if (!$loginType) {
        return response()->json(['error' => 'Invalid login input format'], 422);
    }

    // Clean up the login input for phone or WhatsApp
    if ($loginType === 'phone' || $loginType === 'whatsapp') {
        $loginInput = preg_replace('/\D/', '', $loginInput);
    }

    Log::info("Login attempt with $loginType: $loginInput");

    try {
        $user = null;

        // Retrieve user based on the determined login type
        if ($loginType === 'Bill_no') {
            $user = User::where('Bill_no', $loginInput)->first();
        } else {
            $user = User::where($loginType, $loginInput)->first();
        }

        // Attempt email first if no user was found
        if (!$user && $loginType === 'email') {
            $loginType = 'phone';
            $loginInput = preg_replace('/\D/', '', $loginInput);
            $user = User::where($loginType, $loginInput)->first();
        }

        // Attempt phone if no user was found
        if (!$user && $loginType === 'phone') {
            $loginType = 'whatsapp';
            $user = User::where($loginType, $loginInput)->first();
        }

        // Check if user is found and active
        if (!$user) {
            Log::warning("User not found with $loginType: $loginInput");
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if ($user->status !== 'active') { // Check if user status is active
            Log::warning("Inactive user attempted to log in: " . $user->id);
            return response()->json(['error' => 'Inactive user. Please contact support.'], 403);
        }

        Log::info("User found: " . $user->id);

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            Log::warning("Incorrect password for user ID: " . $user->contact_name);
            return response()->json(['error' => 'Incorrect password'], 401);
        }

        $token = auth()->login($user);

        return response()->json([
            'message' => 'Login Successful',
            'name' => $user->contact_name,
            'role' => $user->role,
            'id' => $user->id,
            'token' => $token,
        ]);

    } catch (\Exception $e) {
        Log::error("Exception during login: " . $e->getMessage());
        return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
    }
}

    
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        return response()->json([
    'company_logo' => $user->company_logo,
    'company_logo_header' => $user->company_logo_header
]);

    }

  
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
  
        return response()->json(['message' => 'Successfully logged out']);
    }
  
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
  
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}