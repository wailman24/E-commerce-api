<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
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
        $user = Auth::user();
        $orders = Order::all()
            ->where('user_id', $user->id)
            ->where('is_done', true);
        return OrderResource::collection($orders);
    }

    public function getOrdersCountChartData()
    {
        $data = DB::table('orders')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as ordersCount')
            ->where('is_done', true) // Optional: filter only completed orders
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    public function getCardsData()
    {
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
            $orders = Order::where('seller_id', $user->id)->where('is_done', true)->get();
            $pendingOrders = Order::where('seller_id', $user->id)->where('is_done', false)->count();

            return response()->json([
                'role' => 'seller',
                'myProducts' => DB::table('products')->where('seller_id', $user->id)->count(),
                'myOrders' => $orders->count(),
                'myRevenue' => $orders->sum('total'),
                'myPendingOrders' => $pendingOrders,
            ]);
        }

        // return response()->json(['error' => 'Unauthorized'], 403);
    }
}
