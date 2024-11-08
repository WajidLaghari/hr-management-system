<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Constants\Status;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if (auth()->check()) {

            if (auth()->user()->role === 'admin') {
                $request->validate([
                    'name' => 'required',
                    'email' => 'required|email|unique:users|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                    'password' => 'required',
                    'role' => 'required|in:hr'
                ]);
            }

            else {
                return response()->json(['message' => 'Unauthorized'], Status::UNAUTHORIZED);
            }
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'User created successfully!'], Status::SUCCESS);
    }

    public function hrSelfRegister(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'password' => 'required',
            'role' => 'required|in:hr'
        ]);

        $existingUser = User::where('email', $request->input('email'))->first();
        if ($existingUser) {
            return response()->json(['message' => 'HR user already exists'], Status::INVALID_REQUEST);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => 'hr',
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'HR registered successfully!'], Status::SUCCESS);
    }

    public function updateUserStatus(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'hr') {
            return response()->json(['message' => 'Unauthorized'], Status::UNAUTHORIZED);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Status::NOT_FOUND);
        }

        $newStatus = $request->input('status');

        if (!in_array($newStatus, ['pending', 'approved', 'blocked'])) {
            return response()->json(['message' => 'Invalid status'], Status::BAD_REQUEST);
        }

        if ($user->status === $newStatus) {
            return response()->json(['message' => 'User is already ' . $newStatus], Status::CONFLICT);
        }

        $user->status = $newStatus;
        $user->save();

        $messages = [
            'pending' => 'User updated to pending',
            'approved' => 'User approved',
            'blocked' => 'User blocked',
        ];

        return response()->json(['message' => $messages[$newStatus]], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'password']);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || $user->status !== 'approved') {
            return response()->json(['message' => 'Invalid credentials or account not approved'], Status::UNAUTHORIZED);
        }

        if(!in_array($user->role,['admin', 'hr'])){
            return response()->json(['message' => 'Access denied. Only HR and Admin can login.'], Status::FORBIDDEN);
        }

        if (!auth()->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], Status::UNAUTHORIZED);
        }

        $user = auth()->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer'], Status::SUCCESS);
    }

    public function showAllHRS()
    {
        $user = User::where('role', 'hr')->get();
        return response()->json(['users' => $user ], Status::SUCCESS);
    }

    public function showHR($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], Status::UNAUTHORIZED);
        }

        $hr = User::where('role', 'hr')->find($id);

        if (!$hr) {
            return response()->json(['message' => 'HR not found'], Status::NOT_FOUND);
        }

        return response()->json(['hr' => $hr], Status::SUCCESS);
    }

    public function updateHR(Request $request, $id)
    {
        if(auth()->user()->role !== 'admin')
        {
            return response()->json(['message' => 'Unauthorized'], Status::UNAUTHORIZED);
        }

        $user = User::find($id);

        if(!$user)
        {
            return response()->json(['message' => 'HR not found'], Status::NOT_FOUND);
        }

        $request->validate([
            'name' => 'sometimes|required',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required',
            'role' => 'sometimes|required|in:employee,hr',
        ]);

        if($request->input('password'))
        {
            $user->password = Hash::make($request->input('password'));
        }

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json(['message' => 'HR updated successfully!'], Status::SUCCESS);
    }

    public function deleteHR($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'HR not found'], Status::NOT_FOUND);
        }

        $user->delete();
        return response()->json(['message' => 'HR deleted successfully'], Status::SUCCESS);

    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully!'], Status::SUCCESS);
    }
}
