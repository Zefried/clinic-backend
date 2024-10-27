<?php

namespace App\Http\Controllers;

use App\Models\LabModel;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LabController extends Controller
{
    
    public function addLab(request $request){
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'registrationNo' => 'required',
            'buildingNo' => 'required',
            'landmark' => 'required',
            'workDistrict' => 'required',
            'state' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'validation_error' => $validator->messages(),
            ]);
        }

        DB::beginTransaction();

        try{

            $labUserData = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'user_type' =>  $request->input('profession'),
                'phone' => $request->phone,
                'unique_user_id' => $this->generateUniqueUserId(),
                'password' => $request->password,
                'pswCred' => $request->pswCred,
                'role' =>  $request->input('profession'),
            ]);

            if($labUserData){

                $labModelData = LabModel::create([
                    'name' => $labUserData->name,
                    'email' => $labUserData->email,
                    'phone' => $labUserData->phone,
                    'registrationNo' => $request->registrationNo,
                    'buildingNo'=> $request->buildingNo,
                    'district' => $request->workDistrict,
                    'landmark' => $request->landmark,
                    'state' => $request->state,
                    'lab_account_request' => false,
                    'lab_unique_id' => $labUserData->unique_user_id,
                    'user_id' => $labUserData->id
                ]);

                
                DB::commit();

                return response()->json([
                    'status' => 201,
                    'message' => 'Account created successfully',
                    'lab_data' => $labModelData,
                ]);
            }


            return response()->json([
                'status' => 403,
                'message' => 'Oops, failed to create an account, provide correct information',
            ]); 

        
        }catch(Exception $e){
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'fatal error check console',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function fetchLabData(Request $request)
    {
        try {
            // Set default records per page or use query parameter value
            $recordsPerPage = $request->query('recordsPerPage', 10);

            // Fetch paginated data for lab accounts
            $labAccountData = LabModel::where('disable_status', '!=', '1')
            ->paginate($recordsPerPage);

            // Check if any data was found
            if ($labAccountData->isEmpty()) {
                return response()->json([
                    'status' => 204,
                    'message' => 'No lab data found',
                ]);
            }

            // Return paginated data with additional pagination details
            return response()->json([
                'status' => 200,
                'listData' => $labAccountData->items(), // Paginated items
                'message' => 'Total account data found: ' . $labAccountData->total(),
                'total' => $labAccountData->total(),
                'current_page' => $labAccountData->currentPage(),
                'last_page' => $labAccountData->lastPage(),
                'per_page' => $labAccountData->perPage(),
            ]);

        } catch (Exception $e) {
            // Handle exceptions with a 500 status
            return response()->json([
                'status' => 500,
                'message' => 'Failed to fetch account data. Please check the console for errors.',
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function fetchSingleLabData($id){

        try{

            $labAccountData = LabModel::where('id', $id)->get();
           
            return response()->json([
                'status' => 200,
                'message' => 'Total account data found: ' . $labAccountData->count(),
                'listData' => $labAccountData,
            ]);

        }catch(Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'fetal error please view console',
                'error' => $e->getMessage(),
            ]);
        }
       
       
    }




    public function updateLabUser($id, Request $request) {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'registrationNo' => 'required|string',
            'buildingNo' => 'required|string',
            'district' => 'required|string',
            'landmark' => 'required|string',
            'state' => 'required|string',
            'phone' => 'nullable|string', // Phone is optional
        ]);
    
        if ($validator->fails()) {
            return response()->json(['validation_error' => $validator->messages()]);
        }
    
        DB::beginTransaction();
    
        try {
            // Get the lab account data
            $accountData = LabModel::where('id', $id)->first();
    
            // Prepare update data for LabModel
            $updateAccountData = $accountData->update([
                'name' => $request->input('name'),
                'registrationNo' => $request->input('registrationNo'),
                'buildingNo' => $request->input('buildingNo'),
                'district' => $request->input('district'),
                'landmark' => $request->input('landmark'),
                'state' => $request->input('state'),
            ]);
    
            if ($updateAccountData) {
                // Find the associated user account
                $userAccount = User::where('id', $accountData->user_id)->first();
    
                // Update user account details
                $userAccount->update([
                    'name' => $request->input('name'),
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'status' => 200,
                'message' => 'User and Lab details updated successfully',
            ]);
    
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Fatal Error during update',
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    


    public function disableLabData($id) {
        DB::beginTransaction();
    
        try {
            $labWorkData = LabModel::find($id);
    
            // Check if the lab data exists
            if (!$labWorkData) {
                return response()->json([
                    'status' => 404,
                    'message' => 'lab data not found'
                ]);
            }
    
            // Update the lab's disable status
            $labDataUpdated = $labWorkData->update([
                'disable_status' => 1,
            ]);
    
            // Check if the lab data update was successful
            if ($labDataUpdated) {
                // Fetch the associated user data
                $userData = User::where('id', $labWorkData->user_id);
    
                // Update the user's disable status
                $updated = $userData->update([
                    'disable_status' => 1,
                ]);
    
                // Check if the user data update was successful
                if ($updated) {
                    DB::commit();
                    return response()->json([
                        'status' => 200,
                        'message' => 'Item disabled successfully'
                    ]);
                } else {
                    // If the user update fails, roll back and return error
                    DB::rollBack();
                    return response()->json([
                        'status' => 500,
                        'message' => 'Failed to update account disable status'
                    ]);
                }
            } else {
                // If the lab data update fails, roll back and return error
                DB::rollBack();
                return response()->json([
                    'status' => 500,
                    'message' => 'Failed to update account disable status'
                ]);
            }
    
        } catch (Exception $e) {
            // Rollback transaction and return error on exception
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Fatal error',
                'error' => $e->getMessage(),
            ]);
        }
    }
    

    public function changeLabPsw($id, Request $request){

        try {
                $newPsw = User::where('id', $id)->update([
                    'password' => bcrypt($request->input('pswCred')),
                    'pswCred' => $request->input('pswCred'),
                ]); //

                if($newPsw){
                    return response()->json([
                        'status' => 200,
                        'success' => true,
                        'message' => 'Password updated successfully'
                    ]);
                }else{
                    return response()->json([
                        'status' => 404,
                        'success' => false,
                        'message' => 'User not found'
                    ]);
                }
    
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error updating password, Contact developer',
                'error' => $e->getMessage(),
            ]);
        }

       
       
    }


    // helper functions space 
    private function generateUniqueUserId() {
        do {
            $uniqueUserId = 'Acc-' . strtoupper(uniqid()); // Generating a new unique ID
        } while (User::where('unique_user_id', $uniqueUserId)->exists()); // Checking for uniqueness
        
        return $uniqueUserId; 
    }


    // test functions space (already in use)

    public function labSearch(request $request){

        $query = $request->input('query');
        
   
        if (empty($query)) {
            return response()->json(['suggestions' => []]);
        }


        $suggestions = LabModel::where('disable_status', '!=', '1')
        ->where(function($subQuery) use ($request) {
            $searchQuery = $request->input('query');
            $subQuery->where('phone', 'like', '%' . $searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $searchQuery . '%')
                    ->orWhere('name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('district', 'like', '%' . $searchQuery . '%');
        })
        ->take(10) 
        ->get(['phone', 'email', 'name', 'district', 'id']);

        return response()->json(['suggestions' => $suggestions]);
        
   
    }
}



 