<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Validator;

class RegisterController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
            'c_password' => 'required|same:password',
            'role' => 'required'
        ],
        [
            'password.required' => 'The password field is required.',
            'password.min' => 'The password should contain at least 8 characters.',
            'password.regex' => 'The password must contain at least one letter, one number, and one special character (!, $, #, %).',
        ]);
   
        if($validator->fails()){ 
            return response()->json(['status' => 403, 'error' => $validator->errors()->toArray()]);   
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['remember_token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
   
        return response()->json(['status' => 200, 'success' => 'Registration Successfull', 'data' => $success]); 
    }
}
