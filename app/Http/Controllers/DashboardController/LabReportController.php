<?php

namespace App\Http\Controllers\DashboardController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LabModel;
use App\Models\PatientAssignedData;

class LabReportController extends Controller
{
    
    public function labDashboardReport(Request $request) {
       
        try {
          
            $user = $request->user();
    
            
            $labData = LabModel::where('user_id', $user->id)->first();
            if (!$labData) {
                return response()->json(['message' => 'Lab data not found'], 404);
            }
    
            $labId = $labData->id;
    
           
            $totalEmployee = Employee::where('lab_id', $labId)->count();

            $totalAssignedPatient = PatientAssignedData::where('lab_id', $labId)->count();

            $totalPendingPatient = PatientAssignedData::where('lab_id', $labId)
                ->where('patient_status', 'pending')
                ->count();

            $totalPaidPatient = PatientAssignedData::where('lab_id', $labId)
                ->where('patient_status', 'paid')
                ->count();
    
            $latestFivePatients = PatientAssignedData::where('lab_id', $labId)
                ->where('patient_status', 'pending')
                ->latest('created_at')
                ->take(5)
                ->get();
    
    
            $reportData = [
                'totalEmployee' => $totalEmployee,
                'totalAssignedPatient' => $totalAssignedPatient,
                'totalPendingPatient' => $totalPendingPatient,
                'totalPaidPatient' => $totalPaidPatient,
                'latestFivePatients' => $latestFivePatients,
            ];
    
            return response()->json([
                'status' => '200',
                'data' => $reportData,
            ]);

        } catch (\Exception $e) {
            // Catch and handle any errors
            return response()->json([
                'status' => '500',
                'message' => 'An error occurred while fetching lab dashboard data',
                'error' => $e->getMessage(),
            ]);
        }
    }
    
}
