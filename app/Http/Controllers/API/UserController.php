<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'              => ['required', 'string', 'max:255'],
                'username'          => ['required', 'string', 'max:255', 'unique:users'],
                'email'             => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password'          => ['required', 'string', new Password],
            ]);

            User::create([
                'name'          => $request->name,
                'username'      => $request->username,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first(); //datanya unik jadi tidak akan duplikasi

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Registered');


        } catch (Exception $error) {
            Return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error'     => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            
            $request->validate([
                'email'     => 'email|required',
                'password'  => 'required'
            ]);

            $credentials = request(['email', 'password']);

            if(!Auth::attempt($credentials)) 
            {
                return ResponseFormatter::error([
                    'message'       => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();

            if(! Hash::check($request->password, $user->password, []))
            {
                throw new \Exception('Invalid Credentials');
            }

            // jika berhasil
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token'      => $tokenResult,
                'token_type'        => 'Bearer',
                'user'              => $user
            ], 'Authenticated');
 
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message'       => 'Something went wrong',
                'error'         => $error,
            ], 'Authentication Failed', 500);
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Data Profile User berhasil diambil');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Updated');
    }

}
