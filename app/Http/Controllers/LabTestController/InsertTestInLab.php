<?php

namespace App\Http\Controllers\LabTestController;

use App\Http\Controllers\Controller;
use App\Models\LabModel;
use App\Models\LabTest;
use App\Models\LabTestCategory;
use App\Models\Test;
use App\Models\TestCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsertTestInLab extends Controller
{
    public function insertLabTest(request $request){

        try{

            DB::beginTransaction();

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
                    $data = LabTest::firstOrCreate(
                        [
                            'lab_test_id' => $test['id'],  // Check if lab_test_id already exists
                            'lab_id' => $labId
                        ],
                        [
                            'lab_test_category_id' => $labTestCategory->test_category_id,
                            'lab_name' => implode(',', $labNames),
                            'lab_test_name' => $test['name'],
                        ]
                    );
    
                    $responses[] = $data;
                    }

                    DB::commit();
    
                    return response()->json([
                        'status' => 200,
                        'message' => 'Data Inserted Successfully',
                        'test_data' => $responses
                    ]);
                }
            }


        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Fatal Error',
                'error' => $e->getMessage(),
            ]);
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
            ->pluck('lab_test_id');

                  
            // Fetch actual tests using the test IDs
            $tests = Test::whereIn('id', $testIds)->get();
           
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


    public function ViewAllTestOfLab($id, Request $request) {

        $recordsPerPage = $request->query('recordsPerPage', 10);
    
        // Collecting all test IDs & category IDs associated with the lab
        $labTestData = LabTest::where('lab_id', $id)->get(['lab_test_id', 'lab_test_category_id']);
        
        
        // Collecting all test IDs to fetch test data from the test table
        $testIds = $labTestData->pluck('lab_test_id')->toArray(); 
        // Collecting all test category IDs to fetch category data from the test category table
        $categoryIds = $labTestData->pluck('lab_test_category_id')->toArray(); 


        // Fetching test data using the collected test IDs and paginating the results
        $testData = Test::whereIn('id', $testIds)
            ->select('id', 'name')  // Selecting only the necessary columns
            ->paginate($recordsPerPage);  // Paginating the results
        

        // Fetching test category data
        $testCategory = TestCategory::whereIn('id', $categoryIds)->get(['id', 'name']);

        
        // Getting the lab name
        $labName = LabModel::where('id', $id)->pluck('name')->first();
    
         return response()->json([
            'status' => 200,
            'data' => [
                'lab_name' => $labName,
                'tests' => $testData->items(),  // Only the items for this page
                'pagination' => [
                    'total' => $testData->total(),
                    'current_page' => $testData->currentPage(),
                    'last_page' => $testData->lastPage(),
                    'per_page' => $testData->perPage(),
                ],
                'categories' => $testCategory
            ]
        ]);

    }


    public function searchTestOfLab(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query)) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = LabTest::where('disable_status', '!=', '1')
            ->where(function($subQuery) use ($query) {
                $subQuery->where('lab_test_name', 'like', '%' . $query . '%')
                ->orWhere('lab_name', 'like', '%' . $query . '%');
            })
            ->take(10)
            ->get(['lab_test_name', 'lab_name', 'lab_test_id']);

        return response()->json(['suggestions' => $suggestions]);
    }

    
    public function RemoveLabTest(Request $request)
    {
        // Retrieve the lab test row by 'lab_test_id' from the request
        $labTestRow = LabTest::where('lab_test_id', $request->id)->first();

        // Check if the lab test row exists
        if (!$labTestRow) {
            return response()->json([
                'status' => 404,
                'message' => 'Lab test not found'
            ]);
        }

        // Attempt to delete the row and check the result
        $delete = $labTestRow->delete();

        if ($delete) {
            return response()->json([
                'status' => 200,
                'message' => 'Item removed successfully'
            ]);
        } else {
            // If deletion fails for some reason, return an error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to remove item'
            ]);
        }
    }


    
    
}
