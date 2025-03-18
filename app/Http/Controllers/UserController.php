<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Validation\ValidationException;


class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                //'role_id' => 'required|exists:roles,id'
            ]);
            $hashedpass = Hash::make($request->password);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $hashedpass,
                'role_id' => 3
            ]);
            event(new Registered($user));
            $token = $user->createToken("auth_token")->plainTextToken;
            //return new UserResource($user);
            return response()->json([
                'status' => true,
                'message' => 'User registered Successfully',
                'token' => $token
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            $user = User::where('email', $request->email)->first(); // ✅ Returns a User model

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
            // $user = Auth::user();
            //$user = DB::table('users')->where('email',  $request->email)->first();

            $token = $user->createToken("auth_token")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $token
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $user = $request->user();

        if ($user) {
            // Supprimer le token d'authentification actuel
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not authenticated'
        ], 401);
    }

    public function delete($id)
    {
        $user = User::where('id', $id)->first();

        $user->delete();
        return response()->json([
            'message' => 'deleted'
        ]);
    }

    public function getuser()
    {
        $user = Auth::user();
        return new UserResource($user);
    }
}
