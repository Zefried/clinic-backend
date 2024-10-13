<?php

namespace App\Services;


use App\Models\GoogleAuthUser;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    
    public function handleCallback($provider) 
    {
        
        try {
        
            $socialUser = Socialite::driver($provider)->stateless()->user();

            $userData = User::where('email', $socialUser->email)->first();
            // return response()->json($socialUser);

            if (!$userData) {
                $newUser = $this->createNewUser($socialUser);
                $token = $this->generateNewToken($newUser);
                $this->createSocialAuthUser($socialUser, $newUser, $token);
               
                return $this->redirectWithToken($newUser, $token);

            } else {

                $this->createSocialAuthUser($socialUser, $userData);
                $tokenData = $this->createSanctumToken($userData);

                return $this->redirectWithToken($userData, $tokenData->original->plainTextToken);
            }
        } catch (Exception $e) {
            Log::channel('OauthLogs')->error("Error during $provider auth callback", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function createSocialAuthUser($socialUser, $newUser, $token = null) 
    {
    
        $userSocialData = GoogleAuthUser::where('email', $socialUser->email)->first();
        
        if (!$userSocialData) {

            return GoogleAuthUser::create([
                'name' => $socialUser->name,
                'email' => $socialUser->email,
                'user_id' => $newUser->id,
                'avatar' => $socialUser->avatar,
                'refreshToken' => $socialUser->refreshToken,
                'profile_id_code' => $socialUser->idCode ?? null,
                'userOAuthId' => $socialUser->attributes['id'],
            ]);
        } 
        else {
            return GoogleAuthUser::where('email', $socialUser->email)->update([
                'refreshToken' => $socialUser->refreshToken,
            ]);
        }
    }

    public function createSanctumToken($userData)
    {
        try {
            
            $tokenData = $userData->tokens()->where('tokenable_id', $userData->id)->first();
            if (!$tokenData) {
                $token = $userData->createToken($userData->email);

                return response()->json($token);
            } else {

                $userData->tokens()->where('tokenable_id', $userData->id)->delete();
                $newToken = $userData->createToken($userData->email);

                return response()->json($newToken);
            }
        } catch (Exception $e) {
            Log::channel('OauthLog.log')->error('Failed to create sanctum token using helper function', ['error' => $e->getMessage()]);
        }
    }

    public function userLogout() 
    {

        $user = User::find(9);
        $test = $user->tokens()->where('id', 9)->delete();

        return response()->json($test);
    }

    private function createNewUser($socialUser) 
    {
        
        try {
            return User::create([
                'name' => $socialUser->name,
                'email' => $socialUser->email,
                'role' => 'user',
            ]);
    
           
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }

    }

    private function generateNewToken($user) 
    {
        try{
            return $user->createToken($user->email)->plainTextToken;
        }catch(Exception $e){
            return response()->json($e->getMessage());
        }
    
    }

    private function redirectWithToken($user, $token) 
    {

        $tokenJson = json_encode([
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'token' => $token,
        ]);

        return Redirect::away('http://localhost:3000/login?token=' . urlencode($tokenJson));
    }
    
    public function register(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:2',
            ]);

            if($validator->fails()){
                return response()->json($validator->messages());
            }
            
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user'
            ]);

            if($newUser){
                return response()->json([
                    'status' => 200,
                    'message' => 'success',
                    'user_data' => $newUser,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 409,
                'error' => $e->getMessage(),
            ]);
        }
    }

    
}

























?>