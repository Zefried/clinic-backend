<?php

namespace App\Http\Controllers\LabMasterController;

use App\Http\Controllers\Controller;

use App\Models\TestCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class TestCategoryController extends Controller
{
    public function addTestCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_category_name' => 'required',
            'description' => 'nullable',
            'status' => 'nullable'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => '400',
                'validation_error' => $validator->messages(),
            ]);
        }

        try{

          $testCategoryData = TestCategory::create([
            'name' => $request->test_category_name,
            'description' => $request->description,
            'status' => $request->status,
          ]);

           if ($testCategoryData){
                return response()->json([
                    'status' => 200,
                    'message' => 'Test Category Added Successfully',
                    'test_category_data' => $testCategoryData,
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

    public function fetchTestCategory(Request $request){
        try {
            $recordsPerPage = $request->query('recordsPerPage', 10);
    
            // Fetch paginated data from the model
            $testCategoryData = TestCategory::where('disable_status', '!=', '1')
                ->paginate($recordsPerPage, ['id', 'name']);
    
            if ($testCategoryData->isNotEmpty()) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Total Test Category Found: ' . $testCategoryData->total(),
                    'test_category_data' => $testCategoryData->items(), // Return the paginated items
                    'total' => $testCategoryData->total(),
                    'current_page' => $testCategoryData->currentPage(),
                    'last_page' => $testCategoryData->lastPage(),
                    'per_page' => $testCategoryData->perPage(),
                ]);
            } else {
                return response()->json([
                    'status' => 204,
                    'message' => 'No test categories found',
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
    

    public function editTestCategory($id){
        try{
            $testCategoryData = TestCategory::find($id);
            if($testCategoryData){
                return response()->json([
                    'status' => 200,
                    'message' => 'Test Category Found',
                    'test_category_data' => $testCategoryData,
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

    public function updateTestCategory($id, Request $request){

        try{
            $testCategoryData = TestCategory::find($id);

            if($testCategoryData){

                $updateStatus = $testCategoryData->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'status' => $request->status,
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Test Category Found',
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

    public function disableTestCategory($id){
        try {
     
            $categoryRow = TestCategory::where('id', $id)->first();
    
    
            if (!$categoryRow) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Category not found',
                ]);
            }
    
    
            $disable = $categoryRow->update([
                'disable_status' => true,
            ]);
    
            return response()->json([
                'status' => 200,
                'message' => 'Category disabled successfully',
                'disable_status' => $disable,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while disabling the category',
                'error' => $e->getMessage(),
            ]);
        }
    }
    
}
