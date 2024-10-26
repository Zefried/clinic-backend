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
                'user_type' => 'lab',
                'phone' => $request->phone,
                'unique_user_id' => $this->generateUniqueUserId(),
                'password' => $request->password,
                'pswCred' => $request->pswCred,
                'role' => 'lab',
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
                    'message' => 'Lab account created successfully',
                    'lab_data' => $labModelData,
                ]);
            }


            return response()->json([
                'status' => 403,
                'message' => 'Oops, failed to create a lab account, provide correct information',
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
                'message' => 'Total lab data found: ' . $labAccountData->total(),
                'total' => $labAccountData->total(),
                'current_page' => $labAccountData->currentPage(),
                'last_page' => $labAccountData->lastPage(),
                'per_page' => $labAccountData->perPage(),
            ]);

        } catch (Exception $e) {
            // Handle exceptions with a 500 status
            return response()->json([
                'status' => 500,
                'message' => 'Failed to fetch lab data. Please check the console for errors.',
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function fetchSingleLabData($id){

        try{

            $labAccountData = LabModel::where('id', $id)->get();
           
            return response()->json([
                'status' => 200,
                'message' => 'Total Lab data found: ' . $labAccountData->count(),
                'lab_account_data' => $labAccountData,
            ]);

        }catch(Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'fetal error please view console',
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
                        'message' => 'Failed to update user disable status'
                    ]);
                }
            } else {
                // If the lab data update fails, roll back and return error
                DB::rollBack();
                return response()->json([
                    'status' => 500,
                    'message' => 'Failed to update lab disable status'
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
    


    // helper functions space 
    private function generateUniqueUserId() {
        do {
            $uniqueUserId = 'LAB-' . strtoupper(uniqid()); // Generating a new unique ID
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



 