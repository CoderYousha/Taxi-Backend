<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\AddEmployeeRequest;
use Illuminate\Support\Str;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\DeleteEmployeeRequest;
use App\Http\Requests\GetEmployeeRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function sos() {}

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = User::where('id', $request->user()->id)->first();
        if (!Hash::check($request['oldPassword'], $user->password)) {
            return response()->json([
                'state' => false,
                'message' => ' كلمة المرور غير صحيحة'
            ], 400);
        }
        User::where('id', $user->id)->update([
            'password' => Hash::make($request['newPassword'])
        ]);
        return response()->json([
            'state' => true,
            'message' => 'updated'
        ]);
    }




    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        if (Str::length($validated['number']) == 10)
            $validated['number'] = '+963' . Str::after($validated['number'], '0');
        elseif (STR::length($validated['number']) == 14)
            $validated['number'] = '+' . Str::after($validated['number'], '00');


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
        if (Str::length($request['number']) == 10)
            $request['number'] = '+963' . Str::after($request['number'], '0');
        elseif (Str::length($request['number']) == 14) {
            $request['number'] = '+' . Str::after($request['number'], '00');
        }

        $user = User::where('number', $request['number'])->first();
        if ($user) {
            return response()->json([
                'state' => false,
                'message' => 'Failed to validate data',
                'errors' => [
                    'number' => [
                        'Phone number is already registered'
                    ]
                ]
            ], 422);
        }

        $user = User::create([
            'number' => $request['number'],
            'firstName' => $request['firstName'],
            'lastName' => $request['lastName'],
            'roll' => 'Customer',
            'password' => Hash::make($request['password']),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => [
                'number' => $user['number'],
                'firstName' => $user['firstName'],
                'lastName' => $user['lastName'],
                'roll' => $user['roll']
            ],
            'token' => $token,
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
        $user = $request->user();

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

    public function addEmployee(AddEmployeeRequest $request)
    {
        if (Str::length($request['number']) == 10)
            $request['number'] = '+963' . Str::after($request['number'], '0');
        elseif (STR::length($request['number']) == 14)
            $request['number'] = '+' . Str::after($request['number'], '00');
        User::create([
            'number' => $request['number'],
            'firstName' => $request['firstName'],
            'lastName' => $request['lastName'],
            'password' => Hash::make('Syriataxi@1'),
            'roll' => 'Admin'
        ]);

        return response()->json([
            'state' => true,
            'password' => 'Syriataxi@1'
        ], 201);
    }

    public function updateEmployee(UpdateEmployeeRequest $request)
    {
        $user = User::where('id', $request['id'])->first();
        if ($user['roll'] != 'Admin') {
            return response()->json([
                'state' => false,
                'message' => 'wrong id'
            ], 422);
        }
        $user->update([
            'firstName' => $request['firstName'],
            'lastName' => $request['lastName']
        ]);
        return response()->json([
            'state' => true,
            'message' => 'updated'
        ]);
    }

    public function deleteEmployee(DeleteEmployeeRequest $request)
    {
        $user = User::where('id', $request['id'])->first();
        if ($user['roll'] != 'Admin') {
            return response()->json([
                'state' => false,
                'message' => 'wrong id'
            ], 422);
        }
        $user->forceDelete();
        return response()->json([
            'state' => true,
            'message' => 'deleted'
        ]);
    }

    public function getEmployee(GetEmployeeRequest $request)
    {
        $users = User::whereNot('id', $request->user()->id)
            ->where('roll', 'Admin')
            ->get([
                'id',
                'number',
                'firstName',
                'lastName',
                'roll'
            ]);
        return response()->json([
            'state' => true,
            'employees' => $users
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
