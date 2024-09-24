<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public function index()
    {
        try {

            $posts = Post::where('user_id', auth()->user()->id)->get();

            return response()->json(['status' => 200, 'posts' => $posts]);

        } catch (\Throwable $th) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => 'Internal Server Error']);
        }
    }

    public function store(Request $request)
    {
        try {
            // Check if the user is authenticated
        if (!auth()->check()) {
            return response()->json(['status' => 401, 'error' => 'Unauthorized. Invalid or missing token.'], 401);
        }
        
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'body' => 'required',
            ]);
        
            // Check if validation fails
            if($validator->fails()){ 
                return response()->json(['status' => 403, 'error' => $validator->errors()->toArray()]);
            }
        
            //save new data to database
            $post = new Post();
            $post->title = $request->title;
            $post->body = $request->body;
            $post->status = $request->status;
            $post->user_id = auth()->user()->id;
            $post->save();
        
            return response()->json(['status' => 200, 'success' => 'Post created successfully']);
        
        }  catch (\Exception $e) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => 'Internal Server Error']);
        }
    }

    public function edit($id)
    {
        try {

            $post = Post::where('id', $id)->get();

            return response()->json(['status' => 200, 'post' => $post]);

        } catch (\Throwable $th) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => 'Internal Server Error']);
        }
    }
}
