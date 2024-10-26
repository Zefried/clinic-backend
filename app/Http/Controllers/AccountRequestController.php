<?php

namespace App\Http\Controllers;

use App\Models\Doctors_userData;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountRequestController extends Controller
{
    public function requestSubmission(request $request){
        $validator = Validator::make($request->all(), [
            'profession' => 'required|string',
            'name' => 'required|string',
            // 'age' => 'required|integer',
            // 'sex' => 'required|string',
            // 'relativeName' => 'required|string',
            // 'phone' => 'required|numeric|digits_between:10,15',  
            // 'email' => 'required|email|', 
            // 'registrationNo' => 'required|string',
            // 'village' => 'required|string',
            // 'po' => 'required|string',  // Post Office
            // 'ps' => 'required|string',  // Police Station
            // 'pin' => 'required|string',  // Postal Code (string for flexibility)
            // 'district' => 'required|string',
            // 'buildingNo' => 'required|string',
            // 'landmark' => 'required|string',
            // 'workDistrict' => 'required|string',
            // 'state' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['validation_error'=> $validator->messages()]);
        }

        try{

            $requestCreated = Doctors_userData::create([
                'name' => $request->input('name'),
                'age' => $request->input('age'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'user_type' => $request->input('profession'),
                'sex' => $request->input('sex'),
                'relativeName' => $request->input('relativeName'),
                'registrationNo' => $request->input('registrationNo'),
                'village' => $request->input('village'),
                'po' => $request->input('po'),
                'ps' => $request->input('ps'),
                'pin' => $request->input('pin'),
                'district' => $request->input('district'),
                'buildingNo' => $request->input('buildingNo'),
                'landmark' => $request->input('landmark'),
                'workDistrict' => $request->input('workDistrict'),
                'state' => $request->input('state'),
                'designation' => $request->input('profession'),  
                'account_request' => $request->input('account_request'),
            ]);

            if($requestCreated){

                return response()->json([
                    'status' => 201,
                    'message' => 'Request is submitted successfully'
                ]);

            } else {

                return response()->json([
                    'status' => 400, 
                    'message' => 'Failed to submit the request',
                ]);
            }


        }catch(Exception $e){
            return response()->json([
                'status' => 500,
                'message' => ' server encounters an error during processing',
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json($request);

        
    }   

    public function fetchPendingAccounts(Request $request) {
        try {
        
            $recordsPerPage = $request->query('recordsPerPage', 10);
    
     
            $doctorsData = Doctors_userData::where('account_request', 'pending')
                ->paginate($recordsPerPage);
    
       
            if ($doctorsData->isEmpty()) {
                return response()->json([
                    'status' => 204,
                    'message' => 'No pending accounts found',
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'listData' => $doctorsData->items(), 
                'message' => 'Total pending accounts found: ' . $doctorsData->total(),
                'total' => $doctorsData->total(), 
                'current_page' => $doctorsData->currentPage(),
                'last_page' => $doctorsData->lastPage(), 
                'per_page' => $doctorsData->perPage(), 
            ]);
    
        } catch (Exception $e) {
            // Handle the exception and return an error response
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 500,
                'message' => 'Failed to fetch pending accounts',
            ]);
        }
    }
    

    public function updatePendingAccountInfo($id, Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'phone' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['validation_error' => $validator->messages()]);
        }

        try{

            $userAccountData = Doctors_userData::find($id);

            $userAccountData->update([
                    'name' => $request->input('name'),
                    'age' => $request->input('age'),
                    'phone' => $request->input('phone'),
                    'sex' => $request->input('sex'),
                    'relativeName' => $request->input('relativeName'),
                    'registrationNo' => $request->input('registrationNo'),
                    'village' => $request->input('village'),
                    'po' => $request->input('po'),
                    'ps' => $request->input('ps'),
                    'pin' => $request->input('pin'),
                    'district' => $request->input('district'),
                    'buildingNo' => $request->input('buildingNo'),
                    'landmark' => $request->input('landmark'),
                    'workDistrict' => $request->input('workDistrict'),
                    'state' => $request->input('state'),
                    'designation' => $request->input('designation'),
                    'user_type' => $request->input('designation'),
        

                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Request application updated successfully'
                ]);
            
                
        }catch(Exception $e){

            return response()->json([
                'status' => 500,
                'message' => 'Fatal Error during update, please register the user again',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function acceptPendingAccount(Request $request)
    {
        DB::beginTransaction(); // Start transaction

        try {
            // Extract ID and password from request
            $id = $request->userData['id'];
            $password = $request->userData['password'];

            // Fetch pending account data
            $pendingAccountData = Doctors_userData::where('id', $id)->firstOrFail();

            // Check if user with the same email or phone already exists
            $existingUser = User::where('email', $pendingAccountData->email)
                                ->orWhere('phone', $pendingAccountData->phone)
                                ->first();

            if ($existingUser) {
                // Rollback and return error if user already exists
                DB::rollback();
                return response()->json([
                    'status' => 400,
                    'message' => 'User with this email or phone already exists',
                ]);
            }

            // Create new account
            $newAccount = User::create([
                'name' => $pendingAccountData->name,
                'email' => $pendingAccountData->email,
                'user_type' => $pendingAccountData->user_type,
                'phone' => $pendingAccountData->phone,
                'designation' => $pendingAccountData->designation,
                'unique_user_id' => $this->generateUniqueUserId(),
                'password' => Hash::make($password),
                'pswCred' => $password, 
                'role' => 'user',
            ]);

            if ($newAccount) {
                // Update pending account status
                $pendingAccountData->update([
                    'account_request' => 'accepted',
                    'user_id' => $newAccount->id,
                    'unique_user_id' => $newAccount->unique_user_id,
                ]);

                // Commit transaction after successful operations
                DB::commit();

                return response()->json([
                    'status' => 201,
                    'message' => 'Account Created Successfully',
                    'newAccount' => $newAccount,
                    'pendingAccount' => $pendingAccountData
                ]);
            } else {
                // Rollback and return error if account creation fails
                DB::rollback();
                return response()->json([
                    'status' => 500,
                    'message' => 'Failed to create user account'
                ]);
            }

        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollback();

            return response()->json([
                'status' => 500,
                'message' => 'Fatal error occurred',
                'error' => $e->getMessage()
            ]);
        }
    }



    // helper functions space 
    private function generateUniqueUserId() {
        do {
            $uniqueUserId = 'USER-' . strtoupper(uniqid()); // Generating a new unique ID
        } while (User::where('unique_user_id', $uniqueUserId)->exists()); // Checking for uniqueness
        
        return $uniqueUserId; 
    }
}
