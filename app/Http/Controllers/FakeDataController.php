<?php

namespace App\Http\Controllers;

use App\Models\TestPagi;
use Illuminate\Http\Request;

class FakeDataController extends Controller
{
    public function fetchUsers(Request $request)
    {
        $recordsPerPage = $request->query('recordsPerPage');
         
        $perPage = $recordsPerPage;

         // Fetch paginated data from the model
         $data = TestPagi::paginate($perPage);
 
         // Return the data along with pagination links
         return response()->json([
            'status' => 200,
            'listData' => $data,
         ]);
    }



    public function testLabMiddleware(){
        return response()->json('working lab middleware');
    }


    public function testHospitalMiddleware(){
        return response()->json('working hospital middleware');
    }
}
