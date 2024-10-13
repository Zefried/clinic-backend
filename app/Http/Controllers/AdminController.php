<?php

namespace App\Http\Controllers;

use App\Models\AdminTeam;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    //test controllers no uses in real projects 

    public function addTeam(request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'=>'required',
                'email' =>'required|email',
            ]);

            if($validator->fails()){    
                return response()->json($validator->Messages());
            }else{
                try{
                    $teamData = AdminTeam::create([
                        'name' => $request->input('name'),
                        'email' => $request->input('email'),
                    ]);

                }catch(Exception $e){
                    
                    return response()->json([
                        'status' => 422,
                        'error' => 'Email already exist',
                    ]);
                }
               
                return response()->json([
                    'status' => 200,
                    'message' => 'Team added successfully',
                    'teamData' => $teamData,
                ]);
               
            }

        }catch(Exception $e){
            return response()->json($e->getMessage());
        }
    }

    public function verifyTeam(request $request){
        try{

            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => 400,
                    'message' => $validator->messages(),
                ]);
            }

            $data = AdminTeam::where('email', $request->input('email'))->first();
            
            if(!$data){
               return response()->json([
                'status' => 404,
                'email_status' => false,
               ]); 
            }
            return response()->json([
                'status' => 200,
                'email_status' => true,
            ]);

        }
        catch(Exception $e){
            return response()->json($e->getMessage());
        }
      
    }
}
