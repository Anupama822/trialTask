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

    protected function validateRegisterFields(Request $request){
        return \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
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
