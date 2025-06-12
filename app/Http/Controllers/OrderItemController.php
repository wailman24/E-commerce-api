<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Order_item;
use Illuminate\Http\Request;
use function PHPSTORM_META\type;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Gate;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Product;
use App\Models\Seller;
use GuzzleHttp\Psr7\Response;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function getallitems()
    {
        return OrderItemResource::collection(Order_item::all());
    }

    public function index()
    {
        try {
            $user = Auth::user();
            $order = Order::where('user_id', $user->id)
                ->where('is_done', false)->first();

            if (isset($order)) {
                $items = Order_item::where('order_id', $order->id)->get();
                return response()->json([
                    'data' => OrderItemResource::collection($items),
                    //'message' => 'No active order for this user.'
                ], 200);
            } else {
                return response()->json([
                    'items' => [],
                    'message' => 'No active order for this user.'
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getallselleritems()
    {
        try {
            $user = Auth::user();
            $seller = Seller::where('user_id', $user->id)->first();
            $items = Order_item::join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.seller_id', '=', $seller->id)
                ->select('order_items.*')
                ->get();
            return OrderItemResource::collection($items);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateitemstatus(Request $request, Order_item $Item)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,shipped,delivered'
            ]);

            $Item->update([
                'status' => $request->status
            ]);

            $statuses = $Item->order->items()->pluck('status')->unique();
            $order = $Item->order;

            // Mise à jour du statut global de la commande
            if ($statuses->count() === 1) {
                $order->update(['status' => $statuses->first()]);
            } else {
                if ($statuses->contains('pending')) {
                    $order->update(['status' => 'pending']);
                } elseif ($statuses->contains('shipped')) {
                    $order->update(['status' => 'shipped']);
                } else {
                    $order->update(['status' => 'delivered']);
                }
            }

            return response()->json([
                'item' => new OrderItemResource($Item)
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'product_id' => 'required',
                //'qte' => 'required',
                //'adress_delivery' => 'nullable',
            ]);

            $product = DB::table('products')
                ->select('prix', 'stock')
                ->where('id', $request->product_id)->first();

            //$fields['price'] = ($product->prix) * $fields['qte'];
            $price = $product->prix;
            $order = Order::where('user_id', $user->id)
                ->where('is_done', false)->first();


            /*     if ($request->qte > $product->stock) {
                return response()->json([
                    'status' => false,
                    'message' => 'you should take qte less than or equal to ' . $product->stock
                ], 500);
            }
            */
            //$total = $order->total + $price;
            if (isset($order)) {
                $order_item = Order_item::where('order_id', $order->id)
                    ->where('product_id', $request->product_id)
                    ->first();

                if ($order_item) {

                    if ($order_item->qte == $product->stock) {
                        return response()->json([]);
                    } else {
                        $order_item->update([
                            'qte' => $order_item->qte + 1,
                            'price' => $order_item->price + $product->prix
                        ]);

                        $order->update(['total' => $order->total + $product->prix]);
                        return new OrderItemResource($order_item);
                    }
                }
            } else {
                $order = Order::create([
                    'user_id' => $user->id,
                    'adress_delivery' => 'to be changed',
                    'total' => 0,
                    'status' => 'pending'
                ]);
            }


            $order_item = Order_item::create([
                'product_id' => $request->product_id,
                'order_id' => $order->id,
                'qte' => 1,
                'price' => $price
            ]);

            $order->update(['total' => $order->total + $price]);

            return new OrderItemResource($order_item);
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
    public function update(Request $request, Order_item $order_item)
    {
        try {
            // Gate::authorize('modify',$request->user(), $order_item);
            Gate::authorize('modify', $order_item);

            $product = DB::table('products')
                ->select('stock', 'prix')
                ->where('id', $order_item->product_id)->first();

            $order = Order::where('id', $order_item->order_id)
                ->where('is_done', false)->first();

            if ($request->option == 'inc') {
                if ($order_item->qte == $product->stock) {
                    return "the stock is empty";
                }

                $order_item->update([
                    'qte' => $order_item->qte + 1,
                    'price' => $order_item->price + $product->prix
                ]);

                //big logique mistake
                $order->update(['total' => $order->total + $product->prix]);
            } else {
                if ($request->option == 'dec') {
                    if ($order_item->qte == 1) {
                        return 'delete?';
                    }

                    $order_item->update([
                        'qte' => $order_item->qte - 1,
                        'price' => $order_item->price - $product->prix
                    ]);

                    $order->update(['total' => $order->total - $product->prix]);
                }
            }

            return new OrderItemResource($order_item);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function inc(Request $request, Order_item $order_item)
    {
        try {

            Gate::authorize('modify', $order_item);
            $product = DB::table('products')
                ->select('stock', 'prix')
                ->where('id', $order_item->product_id)->first();

            $order = Order::where('id', $order_item->order_id)
                ->where('is_done', false)->first();

            if ($order_item->qte == $product->stock) {
                return "the stock is empty";
            }

            $order_item->update([
                'qte' => $order_item->qte + 1,
                'price' => $order_item->price + $product->prix
            ]);

            $order->update(['total' => $order->total + $product->prix]);
            return new OrderItemResource($order_item);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function dec(Request $request, Order_item $order_item)
    {
        try {

            Gate::authorize('modify', $order_item);
            $product = DB::table('products')
                ->select('stock', 'prix')
                ->where('id', $order_item->product_id)->first();

            $order = Order::where('id', $order_item->order_id)
                ->where('is_done', false)->first();

            if ($order_item->qte == 1) {
                return 'delete?';
            }

            $order_item->update([
                'qte' => $order_item->qte - 1,
                'price' => $order_item->price - $product->prix
            ]);

            $order->update(['total' => $order->total - $product->prix]);

            return new OrderItemResource($order_item);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function is_in_cart($product_id)
    {
        try {
            $user = Auth::user();
            $order = Order::where('user_id', $user->id)
                ->where('is_done', false)->first();

            if (isset($order)) {
                $exists = Order_item::where('order_id', $order->id)
                    ->where('product_id', $product_id)
                    ->exists();

                if ($exists) {
                    return response()->json(['exists' => $exists]);
                } else {
                    return response()->json(['message' => 'does not exist']);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Something went wrong: ' . $th->getMessage()
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order_item $order_item)
    {
        Gate::authorize('modify', $order_item);

        $order_id = $order_item->order_id;
        $order_item->delete();
        $exist = Order_item::where('order_id', $order_id)->exists();

        if (!$exist) {
            Order::where('id', $order_id)->delete();
        } else {
            $total = Order_item::where('order_id', $order_id)->sum('price');
            Order::where('id', $order_id)->update(['total' => $total]);
        }

        return new OrderItemResource($order_item);
    }
}
