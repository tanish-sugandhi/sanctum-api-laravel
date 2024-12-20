<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\UserController;

// Route::post('signup', [AuthController::class, 'signup']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('signup', [UserController::class, 'store']);
Route::post('emailCheck', [UserController::class, 'checkEmailExists']);

// Reset Email
Route::post('sendEmail', [UserController::class, 'sendEmail']);
//Reset password
Route::put('passwordReset/{token}', [UserController::class, 'reset']);
?>