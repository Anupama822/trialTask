<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;

class AuthApiController extends BaseController
{
    public function register(Request $request){

        $validator = $this->validateRegisterFields($request);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages()->first());
        }

        try{

            $userCreated = $this->returnCreatedUser($request);

            return $this->successResponse([
                'user'    => $userCreated['user'],
                'message' => 'User registered successfully.',
            ]);

        } catch(Throwable $e){

            return $this->errorResponse("Server error! Plaase try again later.");

        }
    }

    public function login(Request $request){
        $validator = $this->validateLoginFields($request);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages()->first());
        }
        try{
            $user = User::where('email' , $request->email)->first();
            if ($user && Hash::check($request->password, $user->password)){
                $token = $user->createToken('apiToken')->plainTextToken;
     
                $response = [
                    'user' => $user,
                    'token' => $token,
                    'message'  => "User logged in successfully.",
                ];
        
                return $response;
            }
            else{
                return response()->json([
                    'status' => false,
                    'message' => 'Login Failed. The email or password is incorrect.'
                ]);
            }
        }
        catch(Exception $ex){
            return $this->errorResponse("Server error! Plaase try again later.");
        }
       
    }

    public function changePassword(Request $request){
        $validator = \Illuminate\Support\Facades\Validator::make($request->only('old_password', 'new_password'), [
            'old_password' => 'required|string|min:5',
            'new_password' => 'required|string|min:8',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages()->first());
        }

        try{
            $user = auth()->user();
            if(!(Hash::check($request->old_password, $user->password))){
                return $this->errorResponse("Your password doesnot match with password you had provided. Please Try Again.");
            }

            if(strcmp($request->new_password, $request->old_password) == 0){
                return $this->errorResponse("New password cannot be same as old password. Please Try Again.");
            }

       
            $user->password = bcrypt($request->new_password);
            $user->save();

            return $this->successResponse([
                'user'    => $user,
                'message' => 'Password changed successfully.',
            ]);
        }
        catch(Exception $ex){
            return $this->errorResponse("Server error! Plaase try again later.");
        }
        
    }
    

    protected function validateRegisterFields(Request $request){
        return \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
    }

    protected function validateLoginFields(Request $request){
        return \Illuminate\Support\Facades\Validator::make($request->only('email', 'password'), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8',
        ]);
    }

    protected function returnCreatedUser(Request $request) : array
    {
        $user = new User($request->only('name', 'email'));
        $user->password = bcrypt($request->password);
        $user->save();

        return [
            'user' => $user,
        ];
            
    }
}
