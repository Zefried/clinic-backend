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
}
