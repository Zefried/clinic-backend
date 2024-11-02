<?php

namespace App\Http\Controllers\LabTestController;

use App\Http\Controllers\Controller;
use App\Models\LabModel;
use App\Models\LabTest;
use App\Models\LabTestCategory;
use App\Models\Test;
use App\Models\TestCategory;
use Illuminate\Http\Request;

class InsertTestInLab extends Controller
{
    public function insertLabTest(request $request){

        $labId = $request->lab_id;
        $categoryId = isset($request->categoryData['id']) ? $request->categoryData['id'] : null;

       
        $lab = LabModel::where('id', $labId)->get();
        $labNames = [];

        foreach ($lab as $labData) {
            $labNames[] = $labData->name;
        }


        if ($lab) {

            $labTestCategory = LabTestCategory::firstOrCreate(
                [
                    'test_category_id' => $categoryId,
                    'lab_id' => $labId
                ],
                [
                    'lab_name' => implode(',', $labNames)
                ]
            );
            

            if ($labTestCategory) {
              
                $responses = [];

               foreach ($request->test as $test) {
                $xAll = LabTest::firstOrCreate(
                    [
                        'lab_test_id' => $test['id'],  // Check if lab_test_id already exists
                        'lab_id' => $labId
                    ],
                    [
                        'lab_test_category_id' => $labTestCategory->id,
                        'lab_name' => implode(',', $labNames),
                        'lab_test_name' => $test['name'],
                    ]
                );

                $responses[] = $xAll;
                }

                return response()->json($responses);
            }
        }


    }

    public function ViewAssignedCategories($id) {
        try {

            // Retrieve Assigned Category IDs
            $allCatId = LabTestCategory::where('lab_id', $id)->pluck('test_category_id');
    
            // Retrieve Category Data
            $categoryData = TestCategory::whereIn('id', $allCatId)->get();
    
            // Success Response
            return response()->json([
                'status' => 200,
                'test_category_data' => $categoryData,
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

    public function ViewAssignedTest(Request $request) {
    
        try {


      
            // Fetch all test IDs associated with the lab and category
            $testIds = LabTest::where('lab_id', $request->payLoad['lab_id'])
            ->where('lab_test_category_id',$request->payLoad['category_id'])
            ->pluck('id');
                  
            // Fetch actual tests using the test IDs
            $tests = Test::whereIn('id', $testIds)->get();
            return response()->json($tests);  
    
            // Success Response
            return response()->json([
                'status' => 200,
                'test_data' => $tests,
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
    
    
    
}
