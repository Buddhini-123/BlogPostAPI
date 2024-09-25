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

    public function edit($commentId)
    {
        try {

            //get the details of requested own comment
            $comment = Comment::where('id', $commentId)->where('user_id', auth()->user()->id)->first();

            if($comment){
                return response()->json(['status' => 200, 'comment' => $comment]);
            }else{
                return response()->json(['status' => 200, 'message' => 'Not found']);
            }

        } catch (\Throwable $e) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => 'Internal Server Error']);
        }
    }

    public function update(Request $request, $commentId)
    {
        try {
            // Check if the user is authenticated
        if (!auth()->check()) {
            return response()->json(['status' => 401, 'error' => 'Unauthorized. Invalid or missing token.'], 401);
        }
        
            $validator = Validator::make($request->all(), [
                'body' => 'required',
            ]);
        
            // Check if validation fails
            if($validator->fails()){ 
                return response()->json(['status' => 403, 'error' => $validator->errors()->toArray()]);
            }
        
            //update data to database
            $comment = Comment::where('id', $commentId)->where('user_id', auth()->user()->id)->first();
            if($comment){
                $comment->body = $request->body;
                $comment->save();
            
                return response()->json(['status' => 200, 'success' => 'Comment updated successfully']);
            }else{
                return response()->json(['status' => 200, 'error' => 'Comment not found']);
            }
            
        
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
        
        // Get the comment
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['status' => 404, 'error' => 'Comment not found'], 404);
        }

        // Check if the user is an admin
        $user = auth()->user();
        if ($user->role === 'admin') {
            // Admins can delete any comment
            $comment->delete();
            return response()->json(['status' => 200, 'success' => 'Comment deleted successfully']);
        } elseif ($user->id === $comment->user_id) {
            // Regular users can only delete their own comments
            $comment->delete();
            return response()->json(['status' => 200, 'success' => 'Comment deleted successfully']);
        } else {
            return response()->json(['status' => 403, 'error' => 'You are not authorized to delete this post'], 403);
        }
        
        
        }  catch (\Exception $e) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['status' => 500, 'error' => $e->getMessage()]);
        }
    }
}
