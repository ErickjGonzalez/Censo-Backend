<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = User::select('id', 'email', 'password')
                ->where('email', $request->validated('email'))
                ->first();

            if (!$user || !Hash::check($request->validated('password'), $user->password)) {
                return response()->json([
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            if($user->profile_completed_at === null){
                return response()->json([
                    'message' => 'Falta info del user'
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user'         => $user->makeHidden('password'),
                'access_token' => $token,
                'token_type'   => 'Bearer',
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al procesar la solicitud'.$th->getMessage()
            ], 500);
        }
    }




}
