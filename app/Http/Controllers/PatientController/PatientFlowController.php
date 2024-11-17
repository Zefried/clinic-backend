<?php

namespace App\Http\Controllers\PatientController;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LabModel;
use App\Models\LabTest;
use App\Models\Patient_location_Count;
use App\Models\PatientAssignedData;
use App\Models\PatientData;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PatientFlowController extends Controller
{

    public function patientCardView($id){

        try{
            
            $patientData = Patient_location_Count::where('patient_id', $id)
            ->with('patientData')->get();
        
            return response()->json([
            'status' => 200,
            'patientCountData' => $patientData,
            ]);

        }catch(Exception $e){
            
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong please check network console',
                'error' => $e->getMessage()
            ]);
        }   

          
    }


    public function fetchLabPatientName(Request $request) {
        try {
            $patientId = $request->query('patient_id');
            $labId = $request->query('labId');
    
            if (!$patientId || !$labId) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Invalid or missing parameters',
                ]);
            }
    
            $patientName = PatientData::where('id', $patientId)->pluck('name')->first();
            $labName = LabModel::where('id', $labId)->pluck('name')->first();
    
            if (!$patientName || !$labName) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Record not found',
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'patient_name' => $patientName,
                'labName' => $labName,
            ]);
    
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function fetchLabAssociatedEmployee(Request $request) {
        try {
            $labId = $request->query('labId');
    
            if (!$labId) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Lab ID is required',
                ]);
            }
    
            $employeeData = Employee::where('lab_id', $labId)->get(['name', 'id', 'phone']);
    
            if ($employeeData->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No employees found for the given Lab ID',
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'employeeData' => $employeeData,
            ]);
    
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ]);
        }
    }
    

    public function fetchLabTestAllData($labId) {
        try {

            if (!$labId) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Lab ID is required',
                ]);
            }
    
            $allTestData = LabTest::where('lab_id', $labId)
                ->where('disable_status', '!=', '1')
                ->get(['lab_test_name', 'lab_test_id']);
    
            if ($allTestData->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No lab test data found for the given Lab ID',
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'allTestData' => $allTestData,
            ]);
    
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ]);
        }
    }
    


    public function submitPatientAssignedData(Request $request) {
       
        DB::beginTransaction();
    
        // Validation of incoming request
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required',
            'patient_name' => 'required|string',
            'lab_id' => 'required|string',
            'lab_name' => 'required|string',
            'employee_id' => 'required|string',
            'employee_name' => 'required|string',
            'discount' => 'nullable|numeric',
            'final_discount' => 'nullable|numeric',
            'associated_sewek_id' => 'nullable|integer',
            'disable_status' => 'nullable|boolean',
            'doc_path' => 'nullable|string',
            'test_ids' => 'required|array|min:1',
            'visit' => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages(),
            ]);
        }
    
        try {

            // Retrieving the associated user ID
            $associated_user_id = PatientData::where('id', $request->input('patient_id'))
                ->pluck('associated_user_id')
                ->first();
    
            // Checking if a record with the same `patient_id` already exists
            $existingRecord = PatientAssignedData::where('patient_id', $request->input('patient_id'))->first();
    
            if ($existingRecord) {
                // Calling an update method if needed to update existing data
                return $this->updatePatientAssignedData($existingRecord, $request);
            } 
    
            // Creating a new patient assigned data record
            $patientAssignedData = PatientAssignedData::create([
                'patient_id' => $request->input('patient_id'),
                'patient_name' => $request->input('patient_name'),
                'lab_id' => $request->input('lab_id'),
                'lab_name' => $request->input('lab_name'),
                'employee_id' => $request->input('employee_id'),
                'employee_name' => $request->input('employee_name'),
                'discount' => $request->input('discount'),
                'final_discount' => $request->input('final_discount'),
                'associated_sewek_id' => $associated_user_id,
                'disable_status' => 0,
                'doc_path' => $request->input('doc_path'),
                'test_ids' => json_encode($request->input('test_ids')),  
                'visit' => $request->input('visit'),
            ]);
    
            DB::commit();
    
            return response()->json([
                'status' => 200,
                'message' => 'Patient assigned data saved successfully',
                'data' => $patientAssignedData,
            ]);
    
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'error' => 'Database error: ' . $e->getMessage(),
                'message' => 'There was an issue saving the data. Please try again.',
            ]);
        }
    }
    

    public function updatePatientAssignedData($existingRecord, Request $request) {
        DB::beginTransaction();
    
        try {
            // Increment the current visit count
            $currentVisit = $existingRecord->visit;
            $newVisit = $currentVisit + 1;
    
            // Update fields with new data from the request and the incremented visit count
            $existingRecord->update([
                'patient_name' => $request->input('patient_name'),
                'lab_id' => $request->input('lab_id'),
                'lab_name' => $request->input('lab_name'),
                'employee_id' => $request->input('employee_id'),
                'employee_name' => $request->input('employee_name'),
                'discount' => $request->input('discount'),
                'final_discount' => $request->input('final_discount'),
                'disable_status' => 0,
                'doc_path' => $request->input('doc_path'),
                'test_ids' => json_encode($request->input('test_ids')),
                'visit' => $newVisit,
            ]);
    
            DB::commit();
    
            return response()->json([
                'status' => 200,
                'message' => 'Patient assigned data updated successfully',
                'data' => $existingRecord,
            ]);
    
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'error' => 'Database error: ' . $e->getMessage(),
                'message' => 'There was an issue updating the data. Please try again.',
            ]);
        }
    }
    
    


    public function fetchAssignedPatient(Request $request)
    {
        try {

            // Set default records per page or use query parameter value
            $recordsPerPage = $request->query('recordsPerPage', 10);
    
            // Fetch paginated data for assigned patients
            $assignedPatientData = PatientAssignedData::where('disable_status', '!=', '1')
                ->paginate($recordsPerPage);
    
            // Check if any data was found
            if ($assignedPatientData->isEmpty()) {
                return response()->json([
                    'status' => 204,
                    'message' => 'No assigned patient data found',
                ]);
            }
    
            // Return paginated data with additional pagination details
            return response()->json([
                'status' => 200,
                'listData' => $assignedPatientData->items(),
                'message' => 'Total assigned patient data found: ' . $assignedPatientData->total(),
                'total' => $assignedPatientData->total(),
                'current_page' => $assignedPatientData->currentPage(),
                'last_page' => $assignedPatientData->lastPage(),
                'per_page' => $assignedPatientData->perPage(),
            ]);
    
        } catch (Exception $e) {
            // Handle exceptions with a 500 status
            return response()->json([
                'status' => 500,
                'message' => 'Failed to fetch assigned patient data. Please check the console for errors.',
                'error' => $e->getMessage(),
            ]);
        }
    }
    

    public function searchPatientsByNameAndLocation(Request $request) {
        $query = $request->input('query');
    
        // Early return for empty queries
        if (empty($query)) {
            return response()->json(['results' => []]);
        }
    
        try {
            $results = PatientAssignedData::where('disable_status', '!=', '1')
                ->where(function ($subQuery) use ($query) {
                    $subQuery->where('patient_name', 'like', '%' . $query . '%')
                             ->orWhere('employee_name', 'like', '%' . $query . '%');
                })
                ->take(10)  // Limit results to 10 for efficiency
                ->get(['patient_id', 'patient_name', 'lab_id', 'lab_name', 'employee_name', 'employee_id']);  // Retrieve only relevant fields
    
            return response()->json([
                'status' => 200,
                'results' => $results,
            ]);
    
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Database error: ' . $e->getMessage(),
                'message' => 'There was an issue with the search. Please try again.',
            ]);
        }
    }
    
    


    
}
