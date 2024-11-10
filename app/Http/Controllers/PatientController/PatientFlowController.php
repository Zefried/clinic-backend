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
            'patient_id' => 'required|string',
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
                'disable_status' => $request->input('disable_status'),
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
    

    public function updatePatientAssignedData($existingRecord, $request){
        $allPatientData = PatientAssignedData::all(['id', 'test_ids']);

        // Decode `test_ids` for each record
        $decoded = $allPatientData->each(function ($item) {
            $item->test_ids = json_decode($item->test_ids, true);
        });
        return response()->json($decoded);
    }

    
    


    
}
