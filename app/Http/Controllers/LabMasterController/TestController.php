<?php

namespace App\Http\Controllers\LabMasterController;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

class TestController extends Controller
{

    public function AddTest(Request $request){

        $validator = Validator::make($request->all(), [
            'test_category_id' => 'required',
            'name' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['validation_error' => $validator->messages()]);
        }

        try{

          $testData = Test::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'test_category_id' => $request->test_category_id,
          ]);

           if ($testData){
                return response()->json([
                    'status' => 200,
                    'message' => 'Test Added Successfully',
                    'test_data' => $testData,
                ]);
           }else{
                return response()->json([
                    'status' => 401,
                    'message' => 'Something Went Wrong, Please Try Again',
                ]);
           }
           
        }catch(\Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'fatal error',
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function fetchTestWithId($id, Request $request){

        try {
        $recordsPerPage = $request->query('recordsPerPage', 10);

        // Fetch paginated data from the model
        $testData = TestCategory::where('id', $id)
            ->with('tests')
            ->where('status', '!=', '0')
            ->paginate($recordsPerPage);

        if ($testData->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Total test found: ' . $testData->total(),
                'test_data' => $testData->items(), // Return the paginated items
                'total' => $testData->total(),
                'current_page' => $testData->currentPage(),
                'last_page' => $testData->lastPage(),
                'per_page' => $testData->perPage(),
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'No tests found',
            ]);
        }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Fatal error',
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function editLabTest($id){
        try{

            $testData = Test::find($id);
            if($testData){
                return response()->json([
                    'status' => 200,
                    'message' => 'Test Data Found',
                    'test_data' => $testData,
                ]);
            }else{
                return response()->json([
                    'status' => 403,
                    'message' => 'Something went wrong, please try again'
                ]);
            }
        }catch(\Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'fatal error',
                'error' => $e->getMessage(),
            ]);
        }
        return response()->json($id);
    }

    public function updateLabTest($id, Request $request){

        try{
            $testData = Test::find($id);

            if($testData){

                $updateStatus = $testData->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'status' => $request->status,
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Test Data Updated',
                    'update_status' => $updateStatus,
                ]);
            }else{
                return response()->json([
                    'status' => 403,
                    'message' => 'Something went wrong, please try again'
                ]);
            }
        }catch(\Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'fatal error',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function searchLabTest(Request $request) {
        try{

            $query = $request->input('query');
        
            if (empty($query)) {
                return response()->json(['results' => []]);
            }
        
            $results = Test::where('disable_status', '!=', '1')
                        ->where('name', 'like', '%' . $query . '%')
                        ->take(10)
                        ->get(['id', 'name']);
        
            return response()->json([
                'suggestions' => $results
            ]);

        }catch(Exception $e){
            return response()->json([
                'status' => 200,
                'message' => 'fetal error',
                'error' => $e->getMessage(),
            ]);
        }
        
    }
    
}
