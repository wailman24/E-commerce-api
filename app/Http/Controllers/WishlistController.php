<?php

namespace App\Http\Controllers;

use App\Http\Resources\WishlistResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class WishlistController extends Controller
{
    /**
     * Afficher la wishlist de l'utilisateur.
     */
    public function view_wishlist()
    {
        try {
            $user = Auth::user();


            //$wishlist = $user?->wishlist()->with('product')->get();
            $wishlist = Wishlist::where('user_id', $user->id)->get();

            //if (!$wishlist) return " your wishlist is empty";
            return WishlistResource::collection($wishlist);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function is_in_wishlist($product_id)
    {
        try {
            $user = Auth::user();

            $exists = Wishlist::where('user_id', $user->id)
                ->where('product_id', $product_id)
                ->exists();

            return response()->json(['exists' => $exists]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Something went wrong: ' . $th->getMessage()
            ], 500);
        }
    }


    /**
     * Ajouter un produit à la wishlist.
     */
    public function add_to_wishlist(Request $request)
    {
        try {
            // Valide le produit_id dans la requête et verifie s'il existe
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        $user = Auth::user();

        // Vérifie si le produit est déjà dans la wishlist
        $existing = Wishlist::where('user_id', $user->id)  //where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return response()->json([

                'message' => 'Product Already In The Wishlist.'
            ]);
        }
        // Ajoute à la wishlist
        Wishlist::create([
            'user_id' => $user->id,  //$user->id
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product Added To The Wishlist.'
        ], 201);
    }


    /**
     * Supprimer un produit de la wishlist.
     */
    public function remove_from_wishlist(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }



        /** @var int|null $userId */
        $userId = Auth::id();

        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not Found In The Wishlist.'
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product Removed From The Wishlist.'
        ], 200);
    }
}
