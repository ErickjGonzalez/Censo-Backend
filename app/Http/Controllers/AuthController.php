<?php

namespace App\Http\Controllers;
use App\models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest\LoginRequest;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        try {
            $user = User::select('id','name','email', 'password', 'profile_completed_at')
                ->where('email', $request->validated('email'))
                ->first();

            if (!$user || !Hash::check($request->validated('password'), $user->password)) {
                return response()->json([
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                ...($user->name ? ['name' => $user->name] : []),
                /* 'name'         => $user->name, */
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'complete'     => !is_null($user->profile_completed_at),
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al procesar la solicitud'.$th->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Sesión cerrada correctamente'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
                'error'   => config('app.debug') ? $th->getMessage() : null
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        // Handle user login
    }

    public function resetPassword(Request $request)
    {
        // Handle user login
    }
}
