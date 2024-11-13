<?php

namespace App\Http\Controllers\patientController;

use App\Http\Controllers\Controller;
use App\Models\Patient_location_Count;
use App\Models\PatientData;
use App\Models\PatientLocation;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// patient registration request controller
class PatientRegRequestController extends Controller
{
  
    
    ///////// patient resource creation starts here

    public function addPatientRequest(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'patient_location_id' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['validation_error' => $validator->messages()]);
        }
       
        try {

            $userData = $request->user();
    
            $patientRequestData = PatientData::create([
                'name' => $request->input('name'),
                'patient_location_id' => $request->input('patient_location_id'),
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
                'request_status' => 'pending',
                'associated_user_email' => $userData->email,
                'associated_user_id' => $userData->id,
            ]);
    
            if ($patientRequestData) {
                
                $count = Patient_location_Count::where('patient_id', $patientRequestData->id)->count();
    
                if ($count !== 0) {

                    if (!empty($patientRequestData->patient_card_id)) {
                        return response()->json([
                            'status' => 200,
                            'message' => 'Initial patient data created. No further action required.'
                        ]);
                    } else {
                        $newPatientCardId = $this->generatePatientCardId($patientRequestData->patient_location_id, $userData);
                        $this->createPatientLocationCount($patientRequestData, $userData->id, $newPatientCardId);
    
                        return response()->json([
                            'status' => 201,
                            'message' => 'New patient data and card id created successfully.',
                            'patient_card_id' => $newPatientCardId,
                        ]);
                    }
                } else {

                    $newPatientCardId = $this->generatePatientCardId($patientRequestData->patient_location_id, $userData);
                    $this->createPatientLocationCount($patientRequestData, $userData->id, $newPatientCardId);
    
                    return response()->json([
                            'status' => 201,
                            'message' => 'New patient data and card id created successfully.',
                            'patient_card_id' => $newPatientCardId,
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // Helper function to generate the patient card ID
    private function generatePatientCardId($locationId, $userData) {
        $location = PatientLocation::find($locationId);
    
        if ($location) {
            $locationAbbreviation = strtoupper(substr($location->location_name, 0, 3));
            
            // Pad the location count to two digits
            $locationCount = str_pad(Patient_location_Count::where('location_id', $locationId)->count() + 1, 2, '0', STR_PAD_LEFT);
    
            // Pad the user ID to two digits
            $formattedUserId = str_pad($userData->id, 2, '0', STR_PAD_LEFT);
    
            // Construct the patient_card_id with both padded values
            return $locationAbbreviation . '-' . $locationCount . '-' . $formattedUserId;
            
        }
    
        throw new \Exception("Location not found.");
    }
    
    // Helper function to create a new record in Patient_location_Count
    private function createPatientLocationCount($patientData, $userId, $patientCardId) {
       
        return Patient_location_Count::create([
            'location_id' => $patientData->patient_location_id,
            'patient_id' => $patientData->id,
            'associated_user_id' => $userId,
            'patient_card_id' => $patientCardId,
        ]);

    }
    


    ///////// patient resource creation ends here

    public function fetchAllPatient(Request $request)
    {
        $user = $request->user();
        
        $query = PatientData::where('disable_status', 0);
    
        if ($user->role !== 'admin') {
            $query->where('associated_user_email', $user->email);
        }

        $recordsPerPage = $request->input('recordsPerPage', 10);  // Set default to 10
        $page = $request->input('page', 1);
    
    
        try {
            $patientFetchedData = $query->paginate($recordsPerPage, ['*'], 'page', $page);
    
            return response()->json([
                'status' => 200,
                'listData' => $patientFetchedData->items(),  // No need for null check
                'total' => $patientFetchedData->total(),    // No need for null check
                'message' => 'Data fetched successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage(),  // Provides error message for debugging
            ]);
        }
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

        try{
            $email = $request->selected['email'];
       
            $patientFetchedData = PatientData::where('associated_user_email', $email)->where('disable', 0)->get();
    

            return response()->json([
                'status' => 200,
                'patient_data' => $patientFetchedData,
            ]);

        }catch(Exception $e){
            
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
            ]);
        }
       
    
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


