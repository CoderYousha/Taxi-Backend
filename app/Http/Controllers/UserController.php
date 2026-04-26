<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();


        $user = User::where('number', $validated['number'])->first();

        // التحقق من كلمة المرور
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'state' => false,
                'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة'
            ], 401);
        }
        $carNumber = Driver::where('userId', $user->id)->value('carNumber');
        if ($carNumber != null) {
            $user->carNumber = $carNumber;
        }
        $type = Driver::where('userId', $user->id)->value('type');
        if ($type != null) {
            $user->cartype = $type;
        }
        if ($user->banned) {
            return response()->json([
                'state' => false,
                'message' => 'هذا الحساب محظور'
            ], 403);
        }

        if ($user->tokens()->first()) {
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'state' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'user' => [
                    'number' => $user->number,
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'roll' => $user->roll,
                ],
                'token' =>  $token,
            ]);
        } else {
            $token = $user->createToken('auth_token')->plainTextToken;
            if ($user->roll != 'Driver')
                return response()->json([
                    'state' => true,
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'user' => [
                        'number' => $user->number,
                        'firstName' => $user->firstName,
                        'lastName' => $user->lastName,
                        'roll' => $user->roll,
                        'ChangePasswordNeeded' => false
                    ],
                    'token' => $token
                ]);
            else {
                return response()->json([
                    'state' => true,
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'user' => [
                        'number' => $user->number,
                        'firstName' => $user->firstName,
                        'lastName' => $user->lastName,
                        'roll' => $user->roll,
                        'ChangePasswordNeeded' => false,
                    ],
                    'token' => $token
                ]);
            }
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function register(CreateUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'number' => $validated['number'],
            'firstName' => $validated['firstName'],
            'lastName' => $validated['lastName'],
            'password' => Hash::make($validated['password']),
            'roll' => $validated['roll'] ?? 'user',
        ]);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'state' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $request->user()->currentAccessToken()->delete();


        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'state' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validated = $request->validate([
            'firstName' => 'sometimes|string|max:255',
            'lastName' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:6',
            'banned' => 'sometimes|boolean',
            'roll' => 'sometimes|in:Admin,Driver,Customer',
            'expireDate' => 'nullable|date'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'state' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'state' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $driver = Driver::where('userId', $user->id)->first();

        $responseData = [
            'state' => true,
            'name' => $user->firstName . ' ' . $user->lastName,
            'number' => $user->number,
            'carNumber' => null,
            'cartype' => null
        ];

        if ($driver) {
            $responseData['carNumber'] = $driver->carNumber;
            $responseData['cartype'] = $driver->type;
        }

        return response()->json($responseData);
    }
}
