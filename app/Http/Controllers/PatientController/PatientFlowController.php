<?php

namespace App\Http\Controllers\PatientController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LabModel;
use App\Models\Patient_location_Count;
use App\Models\PatientData;
use Exception;

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
    
    
    


    
}
