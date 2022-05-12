<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Auth;

class LoginController extends Controller
{
    /**
     * Check Login
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @author Ujas Patel
     * @return json object     
    */ 

    public function login(Request $request){
    	$input = $request->all();

    	$rules = array(
    				'email' => 'required|exists:users,email,deleted_at,NULL',
    				'password' => 'required'
    			);

    	$validator = Validator::make($input,$rules);

    	if($validator->fails()){
    		$response = array('status'=>false,'message'=>$validator->errors()->first());
    	}else{

    		$user = User::where('email',$input['email'])->first();

    		if($user){

    			if(!$user->is_active){
    				return $response = array('status'=>false,'message'=>"Your account has been deactivated by administrator. Please contact system admin.");
    			}

                if(is_null($user->role)){
                    return $response = array('status'=>false,'message'=>"Your role has not available. Please contact system admin.");
                }

    			$credentials = $request->only(['email', 'password']);

    			if (!is_null($user) && Auth::attempt($credentials)) {

                    $token = Auth::user()->createToken('7span API Token')->accessToken;

	                $response = [
	                    'status' => true,
	                    'message' => 'Success ! Login successful.',
                        'token' => $token,
                        'data' => Auth::user(), 
	                ];

	            }else{
	                $response = [
	                    'status' => false,
	                    'message' => 'Failed ! Invalid login credentials.'
	                ];
	            }
    		}
    	}

    	return $response;
    }
}
