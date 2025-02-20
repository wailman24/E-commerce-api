<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Order_item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Order_item::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $feilds = $request->validate([
            'product_id' => 'required',
            'qte' => 'required',
            'price' => 'required'
        ]);

        $order = Order::where('user_id', $request->user_id)->where('is_done', false)->first();

        if(!isset($order)){
            $feild = $request->validate(['user_id' => 'required']);
            $feild['adress_delivery'] = 'Beni Messous';
            $feild['total'] = '0';
            $feild['status'] = 'pending';
            $order = Order::create($feild);
        }

        $feilds['order_id'] = $order->id;

        $order_item = Order_item::create($feilds);

        return ['Order' => $order, 'Order_item' => $order_item];

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
        $product = DB::table('products')->where('id', $order_item->id)->first();

        if($request->option == 'inc'){
            if(intval($order_item->qte) == intval($product->stock)){
                return "the stock is empty";
            }
            $feild['qte'] = strval(intval($order_item->qte) + 1);
        }
        else{
            if($request->option == 'dec'){
                if(intval($order_item->qte) == 1){
                    return 'delete?';
                }
                $feild['qte'] = strval(intval($order_item->qte) - 1);
            }
        }

        $order_item->update($feild);

        return $order_item;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order_item $order_item)
    {
        $order_item->delete();

        return 'this item has been deleted';
    }
}
