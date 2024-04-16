<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::all();
        if(count($user) > 0){
            //user exists
            $response= [
                "message" => count($user). " users found",
                "status" => 1,
                "data"=> $user
            ];
        }else{
            //user does not exist
            $response= [
                "message" => count($user). "users found",
                "status" => 0
            ];
        }
        return response()->json($response,200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "name" => ["required"],
            "email" => ["required", "unique:users,email"],
            "password" => ["required", "min:8", "confirmed"],
            "password_confirmation" => ["required"]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {

            $data = [
                "name" => $request->name,
                "email" => $request->email,
                "password" => md5($request->password)
            ];

            DB::beginTransaction();
            try {
                $user = User::create($data);
                DB::commit();

            } catch (\Throwable $th) {
                DB::rollBack();
                $user = null;
            }
            if ($user != null) {
                return response()->json([
                    'message' => 'User Registered Successfully'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to Register user'
                ], 500);
            }

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if(is_null($user)){
            $response = [
                'message'=> 'User not found',
                'status'=> 0
            ];
        }else{
            $response = [
                'message'=> 'User found',
                'status'=> 1,
                'data'=> $user
            ];
        }
        return response()->json($response,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if(is_null($user)){
            $response = [
                'message'=> 'User not found!!!',
                'status'=> 0
            ];
            $respCode = 404;
            return response()->json($response, $respCode);
        }else{
            DB::beginTransaction();
            try {
                $user->name= $request['name'];
                $user->email= $request['email'];
                $user->contact= $request['contact'];
                $user->pincode= $request['pincode'];
                $user->address= $request['address'];
                $user->save();
                DB::commit();
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                $user = null;
            }

            if(is_null($user)){
                $response = [
                    'message'=> 'Internal server error !!!',
                    'status'=> 0,
                    'error_msg' => $th->getMessage()
                ];
                $respCode = 500;
                return response()->json($response, $respCode);
            }else{
                $response = [
                    'message'=> 'User updated successfully',
                    'status'=> 1
                ];
                $respCode = 200;
                return response()->json($response,200); 
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message'=> 'User not found',
                'status'=> 0
            ];
            $respCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $response = [
                    'message'=> 'User deleted successfully',
                    'status'=> 1
                ];
                $respCode = 200;
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();
                $response = [
                    'message'=> 'Internal server error!',
                    'status' => 0
                ];
                $respCode = 500;
            }
        }
        return response()->json($response, $respCode);
        
    }

    public function changePassword($id, Request $request){
        $user = User::find($id);
        if(is_null($user)) {
            $response = [
                'message'=> 'User not found',
                'status'=> 0
            ];
            $resCode = 404;
            return response()->json($response, $resCode);
        }else{
            if($user->password==$request['old_password']){
                if ($request['new_password']==$request['confirm_password']) {
                    DB::beginTransaction();
                    try {
                        $user->password = $request['new_password'];
                        $user->save();
                        DB::commit();
                    } catch (\Throwable $th) {
                        //throw $th;
                        $user = null;
                        DB::rollBack();
                    }
                    if(is_null($user)){
                        $response = [
                            'message'=> 'Internal server error !!!',
                            'status'=> 0
                        ];
                        $respCode = 500;
                        return response()->json($response, $respCode);  
                    }else{
                        $response = [ 
                            'message'=> 'User password updated successfully',
                            'status'=> 1
                         ];
                         $respCode = 200;
                         return response()->json($response, $respCode); 
                    }
                } else {
                    $response = [
                        'message'=> 'New password and confirm password does not match !!!',
                        'status'=> 0
                    ];
                    $resCode = 400;
                    return response()->json($response, $resCode);
                }
                
            }else{
                $response = [
                    'message'=> 'Old password does not match !!!',
                    'status'=> 0
                ];
                $resCode = 400;
                return response()->json($response, $resCode);
            }
        }
    }
}
