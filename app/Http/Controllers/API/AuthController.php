<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use App\Http\Requests\UserRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
class AuthController extends Controller
{
    public function register(UserRequest $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|min:5|max:15|unique:users',
        ]);
       return User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try {
            // Attempt to create the token
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // Something went wrong while attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // All good, return the token
        return response()->json(compact('token'));
    }
    public function user() {
        $user = Auth::user();
        if($user->email == config('app.super_admin_email')) {
            $user['lvl'] = 4;
            return $user;
        }
        if(!empty($user->company = Company::where('admin_id',Auth::id())->first())) {
            $user->lvl = 3;
            return $user;

        }
        if(!empty($user->device =  Device::where('users_id',Auth::id())->first())) {
            $user->lvl = 2;
            return $user;
        }
        $user->lvl = 1;
        return $user;
    }
}
