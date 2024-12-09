<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Helpers\JwtHelper;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ApiResponse;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->save();

        return ApiResponse::success(
            data: $user,
            status: 201,
        );
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return ApiResponse::error(
                message: sprintf('User with %s does not exist', $credentials['email']),
                status: 404,
            );
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => [
                    'message' => 'Invalid credentials (incorrect password)',
                    'code' => 0,
                ]
            ]);
        }

        $user->update(['last_issued_at' => now()]);

        $token = JwtHelper::generateToken([
            'sub' => $user->id,
            'role' => 'user',
            'email' => $user->email,
        ]);
        return ApiResponse::success(
            data: $token,
        );
    }

    public function logout(Request $request)
    {
        //todo
    }

    public function updateUser(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        $validated_user = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users,email,' . $id],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->update($validated_user);
        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    public function deleteUser(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }
        $user->delete();

        return response()->json([
            'message' => 'User successfully deleted',
        ]);
    }

    public function getUser(Request $request, string $id): JsonResponse
    {
        $user = User::where('id', $id)->first();
        if ($user == null) {
            return response()->json([
                'success' => false,
                'error' => 'User does not exist',
            ], 404);
        }

        return response()->json(data: [
            'success' => true,
            'data' => $user,
        ]);
    }

    public function getUsers(Request $request): JsonResponse
    {
        $users = User::all();
        return response()->json(data: [
            'success' => true,
            'data' => $users
        ]);
    }
}
