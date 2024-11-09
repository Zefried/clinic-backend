<?php

namespace App\Http\Controllers\patientController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PatientLocation;
use Exception;

use function PHPSTORM_META\map;

class PatientLocationController extends Controller
{

    public function addPatientLocation(request $request){
        try{

            $locationData = PatientLocation::create([
              'location_name' => $request->location_name,
            ]);

            if($locationData){

                return response()->json([
                    'status' => 200,
                    'message' => 'Patient location is added successfully',
                ]);

            } else{

                return response()->json([
                    'status' => 400,
                    'message' => 'Bad Request',
                ]);
            }

        }catch(Exception $e){

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while adding location name',
                'error' => $e->getMessage(),
            ]);
        }
       
    }

    public function viewPatientLocation(request $request){
        

        try{
            
            // fetching associated doctor or sewek for reference in patient assigned table

            $userData = $request->user()->only(['email', 'id']);


            $recordsPerPage = $request->query('recordsPerPage', 10);

            $patientLocationData = PatientLocation::where('disable_status', '!=', '1')
            ->paginate($recordsPerPage);

            if($patientLocationData){

                return response()->json([
                    'status' => 200,
                    'userData' => $userData,
                    'message' => 'Patient Location Fetched Successfully',
                    'list_data' => $patientLocationData->items(),
                    'total' => $patientLocationData->total(),
                    'current_page' => $patientLocationData->currentPage(),
                    'last_page' => $patientLocationData->lastPage(),
                    'per_page' => $patientLocationData->perPage(),
                ]);

            } else {
                return response()->json([
                    'status' => 204,
                    'message' => 'No Data Found',
                ]);
            }
         

        }catch(Exception $e){

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while fetching location name',
                'error' => $e->getMessage(),
            ]);
        }
        
    }

    public function updatePatientLocation(request $request){
        try {
    
            // Find the location by ID and update
            $locationData = PatientLocation::findOrFail($request->id);


           $locationUpdated = $locationData->update([
                'location_name' => $request->location_name,
            ]);
          
    
            // Check if the update was successful
            if ($locationUpdated) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Patient location updated successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Bad Request: Could not update location',
                ]);
            }
    
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while updating the location',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function disablePatientLocation($id){
        $locationData = PatientLocation::findOrFail($id);

        $locationDisable = $locationData->update([
             'disable_status' => true,
        ]);

        if($locationDisable){
            return response()->json([
                'status' => 200,
                'message' => 'Location disabled successfully',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Location data not found',
            ]);
        }
       
    }

    public function fetchPatientAllLocation(){
        try{
            
            // fetching associated doctor or sewek for reference in patient assigned table

            $patientLocationData = PatientLocation::get(['location_name', 'id']);

            if($patientLocationData){

                return response()->json([
                    'status' => 200,    
                    'message' => 'Patient Location Fetched Successfully',
                    'list_data' => $patientLocationData,
                ]);

            } else {
                return response()->json([
                    'status' => 204,
                    'message' => 'No Data Found',
                ]);
            }
         

        }catch(Exception $e){

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while fetching location name',
                'error' => $e->getMessage(),
            ]);
        }
        
    }
    
}
