<?php

namespace App\Http\Controllers\patientLabController;

use App\Http\Controllers\Controller;
use App\Models\LabModel;
use App\Models\Patient_location_Count;
use App\Models\PatientAssignedData;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class PatientLabController extends Controller
{
    // this controller is only used for all the operations associated with patient and lab 

    public function fetchAssignedPatientLab(Request $request) {

        $user = $request->user();

        $labData = LabModel::where('user_id', $user->id)->first();

        if (!$labData) {
            return response()->json([
                'status' => 204,
                'message' => 'No lab data found',
            ]);
        }

        $query = PatientAssignedData::where('disable_status', '!=', '1')
            ->where('lab_id', $labData->id);

        $recordsPerPage = $request->input('recordsPerPage', 10);  // Default to 10
        $page = $request->input('page', 1);

        try {
            $listData = $query->paginate($recordsPerPage, ['*'], 'page', $page);

            return response()->json([
                'status' => 200,
                'listData' => $listData->items(),
                'total' => $listData->total(),
                'current_page' => $listData->currentPage(),
                'last_page' => $listData->lastPage(),
                'per_page' => $listData->perPage(),
                'message' => 'Data fetched successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function searchAssignedPatientLab(Request $request) {
        $query = $request->input('query');
    
        // Early return for empty queries
        if (empty($query)) {
            return response()->json(['results' => []]);
        }
    
        try {
            $results = PatientAssignedData::where('disable_status', '!=', '1')
                // ->where('assignment_status', '=', 'assigned') // Filter for assigned patients
                ->where(function ($subQuery) use ($query) {
                    $subQuery->where('patient_name', 'like', '%' . $query . '%')
                             ->orWhere('lab_name', 'like', '%' . $query . '%')
                             ->orWhere('employee_name', 'like', '%' . $query . '%');
                })
                ->take(10) // Limit results to 10
                ->get(['patient_id', 'patient_name', 'lab_id', 'lab_name', 'employee_name', 'employee_id', 'test_ids']); // Retrieve only relevant fields
    
            return response()->json([
                'status' => 200,
                'suggestions' => $results,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Database error: ' . $e->getMessage(),
                'message' => 'There was an issue with the search. Please try again.',
            ]);
        }
    }


    public function fetchAssignedPatientLabById($id) {
        try {
            // Fetch patient location count data by patient ID
            $carData = Patient_location_Count::where('patient_id', $id)->first();
        
            // Fetch patient assigned data by patient ID
            $assignedData = PatientAssignedData::where('patient_id', $id)->first();

            $refName = User::where('id', $assignedData->associated_sewek_id)->pluck('name');
        
            // Check if either of the data is not found
            if (!$carData || !$assignedData) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Data not found',
                ]);
            }
        
            // Return both data in the response
            return response()->json([
                'status' => 200,
                'patient_card_data' => $carData,
                'assigned_patient_data' => $assignedData,
                'refName' => $refName, 
                'message' => 'Data fetched successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage(),
            ]);
        }
        
    }

    

}
