<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Validator;

class LoginController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if($validator->fails()){ 
            return response()->json(['status' => 403, 'error' => $validator->errors()->toArray()]);   
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['remember_token'] =  $user->createToken('MyApp')->plainTextToken; 
            $success['name'] =  $user->name;
   
            return response()->json(['status' => 200, 'success' => 'Login Successfull', 'data' => $success]); 
        } 
        else{ 
            return response()->json(['status' => 403, 'error' => 'Invalid username or password']);   
        } 
    }
    
}
