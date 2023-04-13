<?php

namespace App\Http\Controllers;

use auth;
use App\Models\Box;
use App\Models\Like;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LikeController extends Controller
{
    // like or unlike
    public function likeOrUnlike($id)
    {
        $box = Box::find($id);

        if (!$box) {
            return response([
                'message' => 'Box not found.'
            ], 403);
        }


        $like = $box->likes()->where('user_id', auth()->user()->id)->first();

        // if not liked then like
        if (!$like) {
            Like::create([
                'box_id' => $id,
                'user_id' => auth()->user()->id
            ]);

            return response([
                'message' => 'Liked'
            ], 200);
        }
        // else dislike it
        $like->delete();

        return response([
            'message' => 'Disliked'
        ], 200);
    }
      // like or unlike
      public function verifLike($id)
      {
          $box = Box::find($id);
  
          if (!$box) {
              return response([
                  'message' => 'Box not found.'
              ], 403);
          }
  
  
          $like = $box->likes()->where('user_id', auth()->user()->id)->first();
  
          // if not liked then like
          if (!$like) {
          
              return response([
                  'status' => true
              ], 200);
          }
      
          return response([
              'status' => false
          ], 200);
      }




}
