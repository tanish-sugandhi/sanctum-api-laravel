<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Validator;

class AuthController extends Controller
{


    public function signup(Request $request)
    {

        $validateUser = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required|email|unique:users,email",
            "password" => "required"
        ]);
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Something Went Wrong',
                'errors' => $validateUser->errors()->all()
            ], 401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
            '$user' => $user,
        ], 200);
    }
    public function login(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required"
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Authentication Fails',
                'errors' => $validateUser->errors()->all()
            ], 404);
        }
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $authUser = Auth::user();
            return response()->json([
                'status' => true,
                'message' => 'User logged Successfully',
                'token' => $authUser->createToken("API Token")->plainTextToken,
                'token_type' => 'bearer'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Email and Password',
            ], 401);
        }
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'loggout Successfully',
        ], 200);
    }
}
