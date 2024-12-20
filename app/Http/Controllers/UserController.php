<?php

namespace App\Http\Controllers;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validate the user input
        $validUser = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required|email|unique:users,email",
            "password" => "required",
        ]);

        // If validation fails, return detailed errors for each field
        if ($validUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validUser->errors()->toArray()  // Return errors as a structured object
            ], 401);
        }

        // Proceed with user creation if validation passes
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password)
        ]);

        // Return success response
        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
            'user' => $user,  // Fix variable key to 'user'
        ], 200);
    }

    public function sendEmail(Request $request)
    {
        $token = Str::random(64);

        // Save token in password_resets table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );
        if (!$this->validateEmail($request->email)) {
            return $this->failedResponse();
        }

        $this->send($request->email, $token);
        return $this->successResponse($token);
    }
    public function validateEmail($email)
    {
        return !!User::where('email', $email)->first();
    }
    public function failedResponse()
    {
        return response()->json([
            'error' => 'Email does not found'
        ], Response::HTTP_NOT_FOUND);
    }
    public function send($email, $token)
    {
        Mail::to($email)->send(new ResetPasswordMail($token));
        return response()->json([
            'token' => $token,
        ]);
    }
    public function successResponse($token)
    {
        return response()->json([
            'data' => $token
        ], Response::HTTP_OK);
    }
    public function reset(Request $request, $token)
    {


        $request->validate([
            'newPassword' => 'required',
            'confirmPassword' => 'required|same:newPassword'
        ]);


        $passwordReset = DB::table('password_reset_tokens')->where('token', $token)->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid or expired token'], 400);
        }
        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->password = Hash::make($request->newPassword);
        $user->save();
        DB::table('password_reset_tokens')->where('token', $request->token)->delete();
        return response()->json([
            'message' => 'Password reset successfully',
            'newPassword' => $user->password,

        ], Response::HTTP_OK);
    }
}
