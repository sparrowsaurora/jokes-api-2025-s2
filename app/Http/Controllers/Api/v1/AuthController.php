<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * API Version 1 - AuthController
 */

class AuthController extends Controller
{
    public function register(Request $request):JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'=>[
                    'required',
                    'string',
                    'max:255'
                ],
                'email'=>[
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:users'
                ],
                'password'=>[
                    'required',
                    'string',
                    'min:6',
                    'confirmed'
                ],
                'password_confirmation'=>[
                    'required',
                    'string',
                    'min:6',
                ],
            ]
        );

        if ($validator->fails()){
            return ApiResponse::error(
                ['error'=>$validator->errors()],
                'registration details error',
                401
            );
        }

        $user = User::create([
            'name'=>$validator->validated()['name'],
            'email'=>$validator->validated()['email'],
            'password'=>Hash::make($validator->validated()['password']),
        ]);

        $token = $user->createToken('MyAppToken')->plainTextToken;

        return ApiResponse::success(
            [
                'token'=>$token,
                'user'=>$user
            ],
            'user successfully created',
            201
        );
    }
}
