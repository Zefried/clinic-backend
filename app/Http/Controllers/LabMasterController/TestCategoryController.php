<?php

namespace App\Http\Controllers\LabMasterController;

use App\Http\Controllers\Controller;

use App\Models\TestCategory;
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

    public function fetchTestCategory(){

        try{
            $testCategoryData = TestCategory::where('status', '!=', '0')->get(['id', 'name']);
            if($testCategoryData){
                return response()->json([
                    'status' => 200,
                    'message' => 'Total Test Category Found ' .  $testCategoryData->count(),
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

}
