<?php

namespace App\Http\Controllers\patientLabController;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LabModel;
use App\Models\Patient_location_Count;
use App\Models\PatientAssignedData;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

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
    
        // Start the query with common conditions
        $query = PatientAssignedData::where('disable_status', '!=', '1')
            ->where('lab_id', $labData->id);
    
        // Filter by employee ID if provided
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
    
        // Check if the 'paid' parameter is present in the request
        if ($request->has('paid')) {
            // Fetch paid patients
            $query->where('patient_status', 'paid');
        } else {
            // Fetch pending (non-paid) patients
            $query->where('patient_status', '!=', 'paid');
        }
    
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

     
        $userData = $request->user();
    
        // Fetch the lab associated with the user
        $labModel = LabModel::where('user_id', $userData->id)->first();
    
        // Ensure the lab exists before proceeding
        if (!$labModel) {
            return response()->json([
                'status' => 404,
                'message' => 'Lab not found for this user.',
            ]);
        }
    
        // Getting the lab_id from the lab model
        $labId = $labModel->id;
    
        // Get the search query and paid filter
        $query = $request->input('query');
        $isPaid = $request->input('paid', 'false') === 'true'; // Check if 'paid' is true
    
        try {
            // Build the query to filter patients by lab_id and other search conditions
            $patientQuery = PatientAssignedData::where('disable_status', '!=', '1')
                                               ->where('lab_id', $labId)  // Ensure only the current user's lab patients are returned
                                               ->where(function ($subQuery) use ($query) {
                                                   $subQuery->where('patient_name', 'like', '%' . $query . '%')
                                                            ->orWhere('lab_name', 'like', '%' . $query . '%')
                                                            ->orWhere('employee_name', 'like', '%' . $query . '%');
                                               });
    
            // Apply the 'paid' or 'pending' filter
            if ($isPaid) {
                $patientQuery->where('patient_status', 'paid');
            } else {
                $patientQuery->where('patient_status', '!=', 'paid');
            }
    
            // Retrieve the results with a limit of 10
            $results = $patientQuery->take(10)
                                    ->get(['patient_id', 'patient_name', 'lab_id', 'lab_name', 'employee_name', 'employee_id', 'test_ids']);
    
            // If no results are found, return an error message
            if ($results->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No results found for your query.',
                    'query' => $query,
                    'paid' => $isPaid,
                ]);
            }
    
            // Return the search results as a JSON response
            return response()->json([
                'status' => 200,
                'suggestions' => $results,
            ]);
    
        } catch (Exception $e) {
            // Handle errors and return a database error response
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


    /// this function is to handle final submission of patient visit status by lab
    public function submitAssignedPatientDataById($id, Request $request) {
        $patientData = PatientAssignedData::find($id);
    
        // Validate the file input (adjust as needed)
        $validator = Validator::make($request->all(), [
            'final_amount' => 'required|numeric',
            'final_discount' => 'numeric', 
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240', 
            'file_type' => 'nullable|in:hospital,lab',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['validation_error' => $validator->messages()], 400);
        }
    
        try {
            // Initialize the PDF path in case no file is uploaded
            $docPath = null;
    
            // Determine the directory based on file type (hospital or lab)
            $directory = $request->file_type == 'hospital' ? 'pdfs/hospitals' : 'pdfs/labs';
            
            // If the file is provided, first delete the existing file if it exists
            if ($request->hasFile('pdf_file')) {
                // Check if the current record has an existing doc file
                if ($patientData->doc_path && Storage::exists('public/' . $patientData->doc_path)) {
                    // Ensure the file is deleted correctly
                    $deleted = Storage::delete('public/' . $patientData->doc_path);
                    if (!$deleted) {
                        return response()->json([
                            'status' => 500,
                            'message' => 'Failed to delete the existing document file.',
                        ]);
                    }
                }
    
                // Store the new PDF file and get its path
                $docPath = $request->file('pdf_file')->store($directory, 'public');
            }
    
            // Update patient data
            $update = $patientData->update([
                'patient_status' => 'paid',
                'final_amount' => $request->final_amount,
                'final_discount' => $request->final_discount,
                'doc_path' => $docPath, // Update the column name to 'doc_path'
            ]);
    
            if ($update) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Patient data updated successfully',
                    'doc_path' => $docPath,
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Failed to update | Please check the fields or re-try | ensure pdf',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to update patient data',
                'error' => $e->getMessage(),
            ]);
        }
    }


    // fetching all lab employee
    public function fetchAllLabEmployee(Request $request) {
        try {
            $user = $request->user();

          
            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthorized user.',
                ]);
            }

            $labData = LabModel::where('user_id', $user->id)->first();

       
            if (!$labData) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Lab not found for the user.',
                ]);
            }

            $employeeData = Employee::where('lab_id', $labData->id)->get();

       
            if ($employeeData->isEmpty()) {
                return response()->json([
                    'status' => 204,
                    'message' => 'No employees found for the lab.',
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => $employeeData->count() . ' Employees fetched successfully.',
                'data' => $employeeData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch all employees.',
            ]);
        }
    }

    
    
    
    
    

    

}
