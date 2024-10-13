<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class SimpleAuthController extends Controller
{

    // in future create a service and interface, merge three function in one with helper function follow SOLID principal
    
    public function adminLogin(Request $request)
    {

        try{

            $validator = Validator::make($request->all(), [
                'email' => 'required|',
                'password' => 'required|',
            ]);
    
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => '400',
                    'validation_error' => $validator->messages(),
                ]);
            }

            $credentials = $request->only('email', 'password');

            // Check if the user exists
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid email or password',
                ]);
            }

            // Revoking all previous tokens (optional)
            $user->tokens()->delete();

            if($user->role === 'admin'){
                // Creating a new token for the user with admin ability
                $token = $user->createToken('auth_token', ['admin'])->plainTextToken;
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorized to access this route',
                ]);
            }
           
            return response()->json([
                'status' => 200,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user, 
                'role' => $user->role,
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'fatal error during login',
                'error' => $e->getMessage(),
            ]);
        }
       
    }

    public function testMw(){
        return response()->json('admin');
    }

}
