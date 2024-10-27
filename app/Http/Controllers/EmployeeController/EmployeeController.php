<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LabModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class EmployeeController extends Controller
{

    public function addEmployeeAgainstLab($id, Request $request) {
        
        
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'role' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'validation_error' => $validator->messages(),
            ]);
        }
    
        try {
            // Retrieve Lab Account
            $labAccount = LabModel::where('id', $id)->first();
    
            if (!$labAccount) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Lab not found',
                ]);
            }
    
            // Create Employee
            $employeeData = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'lab_id' => $labAccount->id,
                'lab_name' => $labAccount->name,
                'lab_location' => $labAccount->district,
            ]);
    
            // Success Response
            return response()->json([
                'status' => 200,
                'message' => 'Employee Added Successfully',
                'employee_data' => $employeeData,
            ]);
    
        } catch (\Exception $e) {
            // Exception Response
            return response()->json([
                'status' => 500,
                'message' => 'Fatal error',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function fetchEmployee(Request $request)
    {
        try {
            $recordsPerPage = $request->query('recordsPerPage', 10);
    
            // Define the query with the necessary filters
            $query = Employee::query()
                ->where('disable_status', '!=', '1');
    
            // Paginate the results
            $data = $query->paginate($recordsPerPage);
    
            if ($data->isEmpty()) {
                return response()->json([
                    'status' => 204,
                    'message' => 'No records found',
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'listData' => $data->items(),
                'message' => 'Total records found: ' . $data->total(),
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
            ]);
    
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 500,
                'message' => 'Failed to fetch data',
            ]);
        }
    }

    public function fetchSpecificLabEmployees(Request $Request){

        $labId = $Request->id;
        $data = Employee::where('lab_id', $labId)->get();

        return response()->json([
            'status' => 200,
            'listData' => $data,
        ]);
    }
    
    
}
