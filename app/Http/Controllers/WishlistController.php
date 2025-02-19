<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class WishlistController extends Controller
{
    /**
     * Afficher la wishlist de l'utilisateur.
     */
    public function view_wishlist()
    {
        /** @var User $user */
        //$user = Auth::user();


        $wishlist = Wishlist::with('product')->get();        //$user?->wishlists()->with('product')->get();
        return response()->json($wishlist, 200);
    }

    /**
     * Ajouter un produit à la wishlist.
     */
    public function add_to_wishlist(Request $request)
    {
        // Valide le produit_id dans la requête
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Vérifie si l'utilisateur est authentifié
        $user = Auth::user();

        /*if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }*/

        // Vérifie si le produit existe
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['message' => 'Produit introuvable'], 404);
        }

        // Vérifie si le produit est déjà dans la wishlist
        $existing = Wishlist::where('user_id', '1')  //where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Produit déjà dans la wishlist'], 409);
        }

        // Ajoute à la wishlist
        Wishlist::create([
            'user_id' => '1',  //$user->id
            'product_id' => $request->product_id,
        ]);

        return response()->json(['message' => 'Produit ajouté à la wishlist'], 201);
    }
    /**
     * Supprimer un produit de la wishlist.
     */
    public function remove_from_wishlist(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        /** @var int|null $userId */
        $userId = Auth::id();

        /*if (!$userId) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }*/

        $wishlist = Wishlist::where('user_id', '1')   //where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$wishlist) {
            return response()->json(['message' => 'Produit non trouvé dans la wishlist'], 404);
        }

        $wishlist->delete();

        return response()->json(['message' => 'Produit retiré de la wishlist'], 200);
    }
}
