<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OtpController extends Controller
{
    public function sendOtp($email)
    {

        // Generate a 6-digit OTP
        $otpCode = rand(100000, 999999);

        // Store OTP in the database with an expiration time
        Otp::updateOrCreate(
            ['email' => $email],
            [
                'otp' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(5), // OTP expires in 5 minutes
            ]
        );

        // Send OTP via email (modify for SMS if needed)
        Mail::raw("Your OTP is: $otpCode", function ($message) use ($email) {
            $message->to($email)->subject("Your OTP Code");
        });

        return response()->json(['message' => 'OTP sent successfully']);
    }

    /*    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $existingOtp = Otp::where('email', $request->email)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingOtp) {
            $existingOtp->delete();
        }
    } */

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|numeric',
                'name' => 'required|string',
                'password' => 'required|string',
            ]);

            // Retrieve OTP from database
            $otp = Otp::where('email', $request->email)
                ->where('otp', $request->otp)
                ->where('expires_at', '>', now())
                ->first();

            if (!$otp) {
                return response()->json(['message' => 'Invalid or expired OTP'], 400);
            }

            // OTP is valid, so delete it after successful verification
            $otp->delete();

            //register the user after verify the otp
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
                'role_id' => 3
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
            ], 201); // Use 201 for resource creation success

            //return response()->json(['message' => 'OTP verified successfully']);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
