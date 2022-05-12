<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserHobby;
use App\Models\Hobby;
use Validator;
use Hash;

class UserController extends Controller
{
    
    /**
     * Create new user or update existing user details
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @author Ujas Patel
     * @return json object     
    */

    public function store(Request $request)
    {
        $input = $request->all();

        $rules = array(
                    'first_name' => 'required|string|max:185',
                    'last_name' => 'required|string|max:185',
                    'email' => 'required|max:185|unique:users,email,NULL,id,deleted_at,NULL|regex:/(.+)@(.+)\.(.+)/i',
                    'password' => 'required|max:20|regex:/^(?=.*\d)(?=.*[@$!%*#?&_-~<>;])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@$!%*#?&_-~<>;]{8,20}$/',
                    'mobile_no' => 'required|unique:users,mobile_no,NULL,id,deleted_at,NULL|regex:/[6-9]{1}[0-9]{9}/',
                );


        if(isset($input['id'])){
            $rules['email'] = 'required|max:185|unique:users,email,'.$input['id'].',id,deleted_at,NULL|regex:/(.+)@(.+)\.(.+)/i';
            $rules['mobile_no'] = 'required|unique:users,mobile_no,'.$input['id'].',id,deleted_at,NULL|regex:/[6-9]{1}[0-9]{9}/';
            unset($rules['password']);
            unset($input['password']);
        }

        if(request()->hasFile('profile')){
            $rules['profile'] = "required|max:5000|mimes:jpeg,png,jpg,eps,bmp,tif,tiff,webp";
        }

        $message = array(
                        'password.regex' => 'The Password has to meet the following criteria: Must be at least 8 characters long. Must contain at least: one lowercase letter, one uppercase letter, one numeric character, and one of the following special characters !@#$%^&-_+=.',
                        'mobile_no.regex' => 'The mobile number is not valid.'
                    );


        $validator = Validator::make($input, $rules, $message);

        if ($validator->fails()) {
            $response = ['status'=>false,'message'=>$validator->errors()->first()];
        }else{

            $input['role_id'] = 2; // user role id
            $input['is_active'] = 1; // set active status

            if(isset($input['id'])){ // Edit Record

                $user = User::find($input['id']);
                $message = "User details updated successfully.";

            }else{ // Add Record

                $user = new User();
                $message = "New User created successfully.";
                $input['password'] = Hash::make($input['password']);
            }



            $old_profile = file_exists(public_path('sitebucket/users/') . "/" . $user->profile);
            if(request()->hasFile('profile') && $user->profile && $old_profile){
                unlink(public_path('sitebucket/users/') . "/" . $user->profile);
                $input['profile'] = null;
            }

            // Upload Profile Image
            if (request()->hasFile('profile')) {
                $file = $request->file('profile');
                $name = date("YmdHis") . $file->getClientOriginalName();
                request()->file('profile')->move(public_path() . '/sitebucket/users/', $name);
                $input['profile'] = $name;
            }

            $user->fill($input)->save();
            
            $response = ['status' => true, 'message' => $message , 'data' => $user];
        }

        return $response;
    }

    /**
     * Get specifi user details
     *
     * @param $id User id
     *
     * @author Ujas Patel
     * @return json object     
    */
    
    public function show($id){
        $user = User::with('role', 'hobbies')->where('id', $id)->where('role_id', '!=', 1)->first();
        if (!is_null($user)) {
            $response = [
                'status' => true,
                'message' => 'Success ! User details found successfully.',
                'data' => $user
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Failed ! User details not found.'
            ];
        }

        return $response;
    }

    /**
     * Delete specific user
     *
     * @param $id User id
     *
     * @author Ujas Patel
     * @return json object     
    */
    public function delete($id){

        $user = User::where('id', $id)->where('role_id', '!=', 1)->first();
        if (!is_null($user)) {

            $user->delete();

            $response = [
                'status' => true,
                'message' => 'Success ! User deleted successfully.',
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Failed ! User details not found.'
            ];
        }

        return $response;
    }

    /**
     * Get all user role details and filter details
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @author Ujas Patel
     * @return json object     
    */
    public function getAll(Request $request){

        $data = User::with('role', 'hobbies')->where('role_id', '!=', 1);

        if(@$request->filter_search != ""){
            $data->where(function($q) use ($request) {
                $q->orwhere('first_name','LIKE',"%".$request->filter_search."%");
                $q->orwhere('last_name','LIKE',"%".$request->filter_search."%");
                $q->orwhere('email','LIKE',"%".$request->filter_search."%");
                $q->orwhere('mobile_no','LIKE',"%".$request->filter_search."%");
            });
        }

        if(@$request->filter_hobby != ""){
            $data->whereHas('hobbies',function($q) use ($request) {
                $q->where('hobby_id', @$request->filter_hobby);
            });
        }

        $data = $data->orderBy('id', 'desc')->get();

        if (count($data)) {
            $response = [
                'status' => true,
                'message' => 'Success ! User list found successfully.',
                'data' => $data
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Failed ! User list not found.'
            ];
        }

        return $response;
    }


    /**
     * User hobbies update
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @author Ujas Patel
     * @return json object     
    */

    public function updateHobbies(Request $request){
        $input = $request->all();
        $user = auth('api')->user();
        
        $rules = array(
                    'hobby_ids' => 'required'
                );

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            $response = ['status'=>false,'message'=>$validator->errors()->first()];
        }else{
            
            $hobbies = explode(",", $request->hobby_ids);

            $hobby_data = Hobby::pluck('id')->toArray();

            $ids = array();
            foreach ($hobbies as $key => $value) {
                
                if(in_array($value, $hobby_data)){  // allow to store valid hobby id
                    array_push($ids, $value);

                    UserHobby::updateOrCreate(
                                    [
                                        'user_id' => $user->id,
                                        'hobby_id' => $value,
                                    ],
                                    [
                                        'user_id' => $user->id,
                                        'hobby_id' => $value,
                                    ]
                                );
                }
            }

            if(empty($ids)){
                UserHobby::where('user_id', $user->id)->delete();
            }else if(!empty($ids)){
                UserHobby::where('user_id', $user->id)->whereNotIn('hobby_id', $ids)->delete();
            }

            $response = ['status' => true, 'message' => 'Success ! Hobbies details updated successfully.' ];
        }

        return $response;
    }


    /**
     * Get all hobbies records
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @author Ujas Patel
     * @return json object     
    */
    public function getAllHobbies(Request $request){

        $data = Hobby::orderBy('name', 'ASC')->get();
        if (count($data)) {
            $response = [
                'status' => true,
                'message' => 'Success ! Hobby list found successfully.',
                'data' => $data
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Failed ! Hobby list not found.'
            ];
        }

        return $response;
    }
}
