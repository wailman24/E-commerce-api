<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
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
}
