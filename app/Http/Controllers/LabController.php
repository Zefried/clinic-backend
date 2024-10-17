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

    public function fetchLabData(){

        try{

            $labAccountData = LabModel::where('lab_account_request', '!=', 'pending')->get();
           
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


    // helper functions space 
    private function generateUniqueUserId() {
        do {
            $uniqueUserId = 'LAB-' . strtoupper(uniqid()); // Generating a new unique ID
        } while (User::where('unique_user_id', $uniqueUserId)->exists()); // Checking for uniqueness
        
        return $uniqueUserId; 
    }


    // test functions space (already in use)

    public function autoSearch(request $request){
  
        $query = $request->input('query');
        
   
        if (empty($query)) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = LabModel::where('phone', 'like', '%' . $query . '%')
            ->orWhere('lab_unique_id', 'like', '%' . $query . '%')
            ->take(10) 
            ->pluck('phone', 'lab_unique_id'); 

        return response()->json(['suggestions' => $suggestions]);
    }
}



 