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

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $order = Order::select('id')
                      ->where('user_id', $user->id)
                      ->where('is_done', false)->first();

        if(isset($order)){
            $items = Order_item::all()->where('order_id', $order->id);
            return OrderItemResource::collection($items);
        }
        return "your cart is empty";
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'product_id' => 'required',
            'qte' => 'required',
        ]);

        $product = DB::table('products')
                     ->select('prix')
                     ->where('id', $request->product_id)->first();

        $fields['price'] = ($product->prix) * $fields['qte'];

        $order = Order::where('user_id', $request->user()->id)
                      ->where('is_done', false)->first();

        if(!isset($order)){
            $field['user_id'] = $request->user()->id;
            $field['adress_delivery'] = '';
            $field['total'] = 0;
            $field['status'] = 'pending';
            $field['is_done'] = false;
            $order = Order::create($field);
        }

        $fields['order_id'] = $order->id;

        $order_item = Order_item::create($fields);

        // $field['total'] = $order->total + $order_item->price;
        // $order->update($field);
        
        // $order->increment('total', $order_item->price);
        $order->update(['total' => $order->total + $order_item->price]);

        return new OrderItemResource($order_item);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order_item $order_item)
    {
        // Gate::authorize('modify',$request->user(), $order_item);
        Gate::authorize('modify', $order_item);

        $product = DB::table('products')
                     ->select('stock', 'prix')
                     ->where('id', $order_item->product_id)->first();

        $order = Order::where('id', $order_item->order_id)
                      ->where('is_done', false)->first();

        if($request->option == 'inc'){
            if($order_item->qte == $product->stock){
                return "the stock is empty";
            }

            $order_item->update([
                'qte' => $order_item->qte + 1,
                'price' => $order_item->price + $product->prix
            ]);

            $order->update(['total' => $order->total + $product->prix]);
        }
        else{
            if($request->option == 'dec'){
                if($order_item->qte == 1){
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

        if(!$exist){
            Order::where('id', $order_id)->delete();
        }

        return 'this item has been deleted';
    }
}
