<?php

namespace App\Http\Controllers\PatientController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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


    
}
