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

            //get all posts according to the auth user
            $posts = Post::where('user_id', auth()->user()->id)->get();

            if ($posts) {
                return response()->json(['status' => 200, 'posts' => $posts]);
            }else{
                return response()->json(['status' => 200, 'message' => 'No Posts Available']);
            }
            

        } catch (\Throwable $e) {
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

            //get the details of requested post
            $post = Post::where('id', $id)->get();
            if ($post) {
                return response()->json(['status' => 200, 'post' => $post]);
            }else{
                return response()->json(['status' => 404, 'message' => 'No Post Available']);
            }

            

        } catch (\Throwable $e) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => 'Internal Server Error']);
        }
    }

    public function update(Request $request, $id)
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
        
            //update data to database
            $post = Post::find($id);
            $post->title = $request->title;
            $post->body = $request->body;
            $post->status = $request->status;
            $post->save();
        
            return response()->json(['status' => 200, 'success' => 'Post updated successfully']);
        
        }  catch (\Exception $e) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => 'Internal Server Error']);
        }
    }

    public function destroy($id)
    {
        try {
            // Check if the user is authenticated
        if (!auth()->check()) {
            return response()->json(['status' => 401, 'error' => 'Unauthorized. Invalid or missing token.'], 401);
        }
        
        $post = Post::where('id', $id)->delete();
        if ($post == 1) {
            return response()->json(['status' => 200, 'success' => 'Post deleted successfully']);
        }else{
            return response()->json(['status' => 404, 'message' => 'No post available']);
        }
        
        
        }  catch (\Exception $e) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => $e->getMessage()]);
        }
    }

    public function publishedPosts()
    {
        try {

            //get all posts according to the auth user
            $posts = Post::where('status', 'published')
                    ->with('comments.user:id,name as commenter')
                    ->with('user:id,name as author')->get();

            if ($posts) {
                return response()->json(['status' => 200, 'posts' => $posts]);
            }else{
                return response()->json(['status' => 200, 'message' => 'No Posts Available']);
            }
            

        } catch (\Throwable $e) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => 'Internal Server Error']);
        }
    }

    public function search(Request $request)
    {
        try {
            $query = Post::query();

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }
    
            // Search by title if provided
            if ($request->has('title')) {
                $query->where('title', 'LIKE', '%' . $request->input('title') . '%');
            }
    
            $posts = $query->with('comments.user:id,name as commenter')
                        ->with('user:id,name as author')->get();;
    
            if ($posts) {
                return response()->json($posts);
            }else{
                return response()->json(['status' => 200, 'error' => 'No Available Data']);
            }
            
        } catch (\Throwable $e) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => $e->getMessage()]);
        }
    }
}
