<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Check if the user is authenticated
        if (!auth()->check()) {
            return response()->json(['status' => 401, 'error' => 'Unauthorized. Invalid or missing token.'], 401);
        }
        
            $validator = Validator::make($request->all(), [
                'body' => 'required',
                'post_id' => 'required',
            ]);
        
            // Check if validation fails
            if($validator->fails()){ 
                return response()->json(['status' => 403, 'error' => $validator->errors()->toArray()]);
            }
        
            //save new data to database
            $comment = new Comment();
            $comment->body = $request->body;
            $comment->post_id = $request->post_id;
            $comment->user_id = auth()->user()->id;
            $comment->save();
        
            return response()->json(['status' => 200, 'success' => 'Comment added successfully']);
        
        }  catch (\Exception $e) {
            Log::error('An error occurred: ' . $e->getMessage());
            
            // Custom handling for integrity constraint violations
            if (str_contains($e->getMessage(), 'Cannot add or update a child row: a foreign key constraint fails')) {
                return response()->json(['status' => 400, 'error' => 'Invalid post_id. The post does not exist or has been deleted.'], 400);
            }

            return response()->json(['status' => 500, 'error' => $e->getMessage()]);
        }
    }
}
