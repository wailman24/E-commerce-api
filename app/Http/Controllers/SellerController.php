<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Seller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\SellerResource;
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
                $image = $request->file('logo');
                $imagePath = 'uploads/images/' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/images'), $imagePath);
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatestatus(Request $request, $id)
    {
        try {
            $seller = Seller::where('id', $id)->first();

            $useller = $seller->update([
                'status' => $request->status
            ]);
            if ($request->status == 'accepted') {
                $user = User::where('id', $seller->user_id)->first();
                $user->update(['role_id' => '2']);
            }
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
        //
    }
}
