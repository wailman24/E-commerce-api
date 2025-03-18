<?php

namespace App\Http\Controllers;

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

        $user = Auth::user();


        $wishlist = $user?->wishlists()->with('product')->get();
        return response()->json($wishlist, 200);
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



        // Vérifie si l'utilisateur est authentifié
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User Not Authenticated. Please Register At The Link Below',
                'register' => url('/api/register')
            ], 401);
        }

        // Vérifie si le produit est déjà dans la wishlist
        $existing = Wishlist::where('user_id', $user->id)  //where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Product Already In The Wishlist.'
            ], 409);
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

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User Not Authenticated.  Please Register At The Link Below',
                'register' => url('/api/register')
            ], 401);
        }

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
