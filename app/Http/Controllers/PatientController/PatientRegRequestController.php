<?php

namespace App\Http\Controllers\patientController;

use App\Http\Controllers\Controller; 
use App\Models\PatientData;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// patient registration request controller
class PatientRegRequestController extends Controller
{

    public function addPatientRequest(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            // 'sex' => 'required|string',
            // 'relativeName' => 'nullable|string|max:255',
           'phone' => 'required|string|min:10|max:10|unique:patient_data,phone',
            // 'email' => 'nullable|email|max:255',
            // 'identityProof' => 'required',
            // 'village' => 'nullable|string|max:255',
            // 'po' => 'nullable|string|max:255',
            // 'ps' => 'nullable|string|max:255',
            // 'pin' => 'nullable|string|max:6',
            // 'district' => 'required|string|max:255',
            // 'state' => 'required|string|max:255',

        ]);


        if($validator->fails()){
            return response()->json(['validation_error'=> $validator->messages()]);
        }

        try{

            //storing associated doctor or pharma with patient data
            $data = $request->user();
            
            $patientRequestData = PatientData::create([
                'name' => $request->input('name'),
                'age' => $request->input('age'),
                'sex' => $request->input('sex'),
                'relativeName' => $request->input('relativeName'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'identityProof' => $request->input('identityProof'),
                'village' => $request->input('village'),
                'po' => $request->input('po'),
                'ps' => $request->input('ps'),
                'pin' => $request->input('pin'),
                'district' => $request->input('district'),
                'state' => $request->input('state'),
                'unique_patient_id' => $this->generateUniqueUserId(),
                'request_status' => 'pending',
                'associated_user_email' => $data->email,
                'associated_user_id' => $data->id,
            ]);

            if($patientRequestData){
                return response()->json([
                    'status' => 200,
                    'message' => 'Patient Added successfully',
                    'patient_data' => $patientRequestData,
                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'something went wrong when registering patient',
                ]);
            }

        }catch(Exception $e){

            return response()->json([
                'status' => 500,
                'message' => 'fetal error',
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function fetchXUserPatient(request $request){
        $user = $request->user();

        if($user->role === 'admin'){
            $patientFetchedData = PatientData::where('disable_status', 0)->get();
        } else {
            $patientFetchedData = PatientData::where('associated_user_email', $user->email)->where('disable_status', 0)->get();
        }
       

        return response()->json([
            'status' => 200,
            'patient_data' => $patientFetchedData,
        ]);
    }

    public function fetchXUserPendingPatient(request $request){
        $data = $request->user();
        $patientPendingData = PatientData::where('associated_user_email', $data->email)->where('request_status', 'pending')->where('disable', 0)->get();

        return response()->json([
            'status' => 200,
            'patient_data' => $patientPendingData,
        ]);
    }

    public function fetchingAllPatient(request $request){
        $email = $request->selected['email'];
       
        $patientFetchedData = PatientData::where('associated_user_email', $email)->where('disable', 0)->get();

        return response()->json([
            'status' => 200,
            'patient_data' => $patientFetchedData,
        ]);
    }

    public function fetchingUserSpecificPatient(request $request){
        $email = $request->selected['email'];

        $patientPendingData = PatientData::where('associated_user_email', $email)->where('request_status', 'pending')->where('disable', 0)->get();

        return response()->json([
            'status' => 200,
            'patient_data' => $patientPendingData,
        ]);
    }

     //helper function to double check and generate a unique user id for every account
    private function generateUniqueUserId() {
        do {
            $uniquePtId = 'PT-' . strtoupper(uniqid()); // Generating a new unique ID
        } while (User::where('unique_user_id', $uniquePtId)->exists()); // Checking for uniqueness
        
        return $uniquePtId; 
    }

    public function autoSearchUser(request $request){
  
        $query = $request->input('query');
        
   
        if (empty($query)) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = User::where('phone', 'like', '%' . $query . '%')
            ->orWhere('email', 'like', '%' . $query . '%')
            ->take(10) 
            ->pluck('phone', 'email'); 

        return response()->json(['suggestions' => $suggestions]);
    }
    
}
