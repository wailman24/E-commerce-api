<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Seller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\SellerResource;
use App\Http\Resources\UserResource;
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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
                'store' => 'required',
                'phone' => 'required|unique:sellers',
                'adress' => 'required',

                'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);
            $imagePath = null;
            if ($request->hasFile('logo')) {
                $imagePath = $request->file('logo')->store('uploads/images', 'public');
            }

            $seller = Seller::create([
                'user_id' => $user->id,
                'store' => $request->store,
                'phone' => $request->phone,
                'adress' => $request->adress,
                'status' => 'pending',
                'logo' => $imagePath, // Save image path in DB
            ]);

            return new SellerResource($seller);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function updateseller(Request $request, $id)
    {
        try {
            $request->validate([
                'store' => 'required|string',
                'phone' => 'required',
                'adress' => 'required|string',
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Find seller
            $seller = Seller::findOrFail($id);
            if (!$seller) {
                return response()->json(['status' => false, 'message' => 'Seller not found'], 404);
            }
            $imagePath = null;
            if ($request->hasFile('logo')) {
                $imagePath = $request->file('logo')->store('uploads/images', 'public');
                $seller->update(['logo' => $imagePath]);
            }
            // Update seller details
            $seller->update([
                'store' => $request->store,
                'phone' => $request->phone,
                'adress' => $request->adress,
                'logo' => $imagePath
            ]);



            return new SellerResource($seller);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatestatus(Request $request, $id)
    {
        try {
            $seller = Seller::where('id', $id)->first();

            if (!$seller) {
                return response()->json([
                    'status' => false,
                    'message' => 'Seller Not Found.'
                ], 404);
            }
            $useller = $seller->update([
                'status' => $request->status
            ]);
            if ($request->status == 'accepted') {
                $user = User::where('id', $seller->user_id)->first();
                $user->update(['role_id' => '2']);
            }
            $seller = Seller::where('id', $id)->first();
            return new SellerResource($seller);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $seller = Seller::where('id', $id)->first();
        if (!$seller) {
            return response()->json([
                'status' => false,
                'message' => 'Seller Not Found.'
            ], 404);
        }
        $user = User::where('id', $seller->user_id)->first();
        $seller->delete();
        $user->update(['role_id' => '3']);

        return new UserResource($user);
    }
}
