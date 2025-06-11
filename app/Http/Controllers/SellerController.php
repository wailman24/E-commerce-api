<?php

namespace App\Http\Controllers;

use App\Http\Resources\EarningResource;
use App\Http\Resources\PayoutResource;
use App\Models\SellerPayout;
use App\Models\User;
use App\Models\Seller;

use Illuminate\Http\Request;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\SellerResource;
use App\Models\Seller_earning;
//use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class SellerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SellerResource::collection(Seller::all());
    }
    public function getseller($id)
    {
        $seller = Seller::findOrFail($id);
        return new SellerResource($seller);
    }
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized: User not authenticated'
                ], 401);
            }
            if (Seller::where('user_id', $user->id)->first()) {
                return response()->json([
                    'status' => false,
                    'message' => 'you are already a seller'
                ], 500);
            }
            $request->validate([
                'store' => 'required|string',
                'phone' => 'required|unique:sellers',
                'adress' => 'required|string',
                'paypal' => 'required',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);
            $imagePath = null;
            if ($request->hasFile('logo')) {
                $imagePath = $request->file('logo')->store('uploads/logos', 'public');
            }

            $seller = Seller::create([
                'user_id' => $user->id,
                'store' => $request->store,
                'phone' => $request->phone,
                'adress' => $request->adress,
                'status' => 'pending',
                'logo' => $imagePath, // Save image path in DB
                'paypal' => $request->paypal
            ]);

            return new SellerResource($seller);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateseller(Request $request, $id)
    {
        try {
            $request->validate([
                'store' => 'required|string',
                'phone' => 'required|unique:sellers',
                'adress' => 'required|string',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // make logo optional
                'paypal' => 'required'
            ]);

            // Find seller
            $seller = Seller::findOrFail($id);
            if (!$seller) {
                return response()->json(['status' => false, 'message' => 'Seller not found'], 404);
            }
            $imagePath = null;
            if ($request->hasFile('logo')) {
                if ($seller->logo && Storage::disk('public')->exists($seller->logo)) {
                    Storage::disk('public')->delete($seller->logo);
                }
                $imagePath = $request->file('logo')->store('uploads/logos', 'public');
            }

            // Update seller details
            $seller->update([
                'store' => $request->store,
                'phone' => $request->phone,
                'adress' => $request->adress,
                'paypal' => $request->paypal
            ]);

            // Only update the logo if a new file is uploaded
            if ($request->hasFile('logo')) {
                // Delete the old logo if exists (optional cleanup)
                if ($seller->logo && Storage::disk('public')->exists($seller->logo)) {
                    Storage::disk('public')->delete($seller->logo);
                }

                $imagePath = $request->file('logo')->store('uploads/images', 'public');
                $data['logo'] = $imagePath;
            }

            // Update seller with data
            $seller->update($data);

            return new SellerResource($seller);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updatesellerstatus(Request $request, $id)
    {
        try {
            // Validate input
            $request->validate([
                'status' => 'required|in:pending,accepted,rejected',
            ]);

            // Fetch seller
            $seller = Seller::find($id);

            if (!$seller) {
                return response()->json([
                    'message' => 'Seller not found.'
                ], 404);
            }

            // Update seller status
            $seller->status = $request->status;
            $seller->save();

            // Promote user to seller if accepted
            if ($request->status === 'accepted') {
                $user = User::find($seller->user_id);
                if ($user) {
                    $user->role_id = 2; // Assuming 2 = seller
                    $user->save();
                }
            }

            // Return updated seller
            return response()->json([
                'message' => 'Seller status updated successfully.',
                'seller' => new SellerResource($seller)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: ' . $th->getMessage()
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        $seller = Seller::where('id', $id)->first();
        if (!$seller) {
            return response()->json([
                'status' => false,
                'message' => 'Seller Not Found.'
            ], 404);
        }

        if ($seller->logo && Storage::disk('public')->exists($seller->logo)) {
            Storage::disk('public')->delete($seller->logo);
        }

        $user = User::where('id', $seller->user_id)->first();
        $seller->delete();
        $user->update(['role_id' => '3']);

        return new UserResource($user);
    }
    public function getpendingsellers()
    {
        $pendingSellers = Seller::where('status', 'pending')->get();
        return SellerResource::collection($pendingSellers);
    }

    public function getallsellerearnings()
    {
        try {
            $sellers = Seller_earning::all();
            return EarningResource::collection($sellers);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getMyPayouts(Request $request)
    {
        $user = Auth::user();
        $seller = $user->seller;

        if (!$seller) {
            return response()->json(['error' => 'Not a seller'], 403);
        }

        $payouts = SellerPayout::where('seller_id', $seller->id)->orderByDesc('created_at')->get();

        return PayoutResource::collection($payouts);
    }
}
