<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use App\Notifications\EmailVerificationNotification;

class AuthApiController extends BaseController
{
    public function register(Request $request){

        $validator = $this->validateRegisterFields($request);//validating fields

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages()->first());
        }

        try{

            $userCreated = $this->returnCreatedUser($request);//creates user 

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
                    'verified' => $user->hasVerifiedEmail()
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

    public function resetPassword(Request $request){
        $validator = \Illuminate\Support\Facades\Validator::make($request->only('new_password'), [
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages()->first());
        }

        try{
            $user = auth()->user();

            $user->password = bcrypt($request->new_password);
            $user->save();
    
            return $this->successResponse([
                'user'    => $user,
                'message' => 'New Password set successfully.',
            ]);
        }

        catch(Exception $ex){
            return $this->errorResponse("Server error! Plaase try again later.");
        }
       
    }

    public function getUserInfo(Request $request)
    {

        $user = User::find($request->user()->id);

        return response()->json([
            'status'   => true,
            'data'     => $user,
            'verified' => $user->hasVerifiedEmail()
        ]);

    }

    public function verifyEmail($id)
    {
        $user = User::find($id);

        if(!$user){
            return redirect('/')->with(['error' => 'User not found.']);
        }

        if(!$user->is_email_verified) {

            $user->forceFill([
                'email_verified_at' => now() 
            ]);
            $user->update();

        } 

        return redirect('/')->with(['success' => 'Email verified successufully.']);

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

        $user->notify(new EmailVerificationNotification($user));

        return [
            'user' => $user,
        ];
            
    }
}
