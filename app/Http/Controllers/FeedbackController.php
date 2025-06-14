<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string',
            ]);

            $feedback = Feedback::create([
                'user_id' => Auth::user()->id,
                'email' => Auth::user()->email,
                'message' => $request->message,
            ]);

            return response()->json(['success' => true, 'feedback' => $feedback]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getbyuserid($id)
    {
        // $user = User::findOrFail($id);
        $fbks = Feedback::where('user_id', $id)->get();
        return FeedbackResource::collection($fbks);
    }

    public function getallfbks()
    {
        // $user = User::findOrFail($id);

        return FeedbackResource::collection(Feedback::all());
    }
}
