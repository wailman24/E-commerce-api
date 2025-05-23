<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order_item;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Support\Facades\DB;



class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        return OrderResource::collection(Order::all()->where('is_done', true));
    }

    /*  public function getallsellerorders()
    {
        try {
            $user = Auth::user();
            $seller = Seller::where('user_id', $user->id)->first();
            $orders = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.seller_id', '=', $seller->id)
                ->select('orders.*')
                ->get();
            return OrderResource::collection($orders);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
 */
    public function order_history()
    {
        try {
            $user = Auth::user();
            $orders = Order::where('user_id', $user->id)->get();
            return OrderResource::collection($orders);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getOrdersCountChartData()
    {
        $user = request()->user(); // <- This works ONLY IF auth:sanctum is applied
        // ✅ always works

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->role_id === 1) {
            $data = DB::table('orders')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as ordersCount')
                ->where('is_done', true) // Optional: filter only completed orders
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            return response()->json($data);
        } elseif ($user->role_id === 2) {
            $seller = Seller::where('user_id', $user->id)->first();
            $data = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->selectRaw('DATE(orders.created_at) as date, COUNT(*) as ordersCount')
                ->where('products.seller_id', '=', $seller->id)
                ->where('orders.is_done', true) // Optional: filter only completed orders
                ->groupBy(DB::raw('DATE(orders.created_at)'))
                ->orderBy('date')
                ->get();

            return response()->json($data);
        }
    }

    public function getCardsData()
    {
        try {

            $user = request()->user(); // <- This works ONLY IF auth:sanctum is applied
            // ✅ always works

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            if ($user->role_id === 1) {
                $orders = Order::where('is_done', true)->get();
                //$pendingOrders = Order::where('is_done', false)->count();

                return response()->json([
                    'role' => 'admin',
                    'totalOrders' => $orders->count(),
                    'totalRevenue' => $orders->sum('total'),
                    'totalUsers' => DB::table('users')->count(),
                    'totalSellers' => DB::table('sellers')->count(),
                    //'pendingOrders' => $pendingOrders,
                ]);
            } elseif ($user->role_id === 2) {
                // $orders = Order_item::where('seller_id', $user->id)->where('is_done', true)->get();
                $orders = Order_item::whereHas('product', function ($query) use ($user) {
                    $query->where('seller_id', $user->id)
                        ->where('is_valid', true);
                })->get();
                $pendingProducts = Product::where('seller_id', $user->id)->where('is_valid', false)->count();

                return response()->json([
                    'role' => 'seller',
                    'myProducts' => DB::table('products')->where('seller_id', $user->id)->count(),
                    'myOrders' => $orders->count(),
                    'myRevenue' => $orders->sum('price'),
                    'pendingProducts' => $pendingProducts,
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

        // return response()->json(['error' => 'Unauthorized'], 403);
    }
}
