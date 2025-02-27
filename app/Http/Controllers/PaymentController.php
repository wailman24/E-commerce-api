<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Order_item;
use App\Models\Seller;
use Illuminate\Http\Request;
use App\Models\Seller_earning;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentController extends Controller
{
    public function getallpayments()
    {
        return response()->json(Payment::all());
    }

    public function createPayment(Request $request)
    {
        try {
            $user = Auth::user();
            $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);
            $order = Order::where('id', $request->order_id)->first();

            $payment = Payment::create([
                'order_id' => $request->order_id,
                'methode' => 'paypal',
            ]);
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => $order->total // Replace with actual amount
                        ]
                    ]
                ],
                "application_context" => [
                    "return_url" => route('payment.success', ['user_id' => $user->id]),
                    "cancel_url" => route('payment.cancel')
                ]
            ]);
            //dd($response);
            if (isset($response['id']) && $response['id'] != null) {
                return response()->json([
                    'status' => 'success',
                    'approval_url' => $response['links'][1]['href']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Something went wrong!'
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function Success(Request $request)
    {
        try {

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $response = $provider->capturePaymentOrder($request->token);

            //dd($response);
            $user = User::find($request->query('user_id'));

            $payment = DB::table('payments')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->where('orders.user_id', $user->id) // Ensure user_id is from orders table
                ->where('payments.status', 'pending') // Apply status filter after the join
                ->select('payments.id', 'payments.order_id') // Select payments data only
                ->first();
            if ($response['status'] == 'COMPLETED') {

                /// firstful change status to compeleted
                if ($payment) {
                    DB::table('payments')
                        ->where('id', $payment->id)
                        ->update(['status' => 'completed']);
                }

                /// update is_done to true in order table
                DB::table('orders')
                    ->where('id', $payment->order_id)
                    ->update(['is_done' => true]);

                /// start working on sellers_earnings table

                $sellers = DB::table('orders')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('orders.id', $payment->order_id)
                    ->select('products.seller_id')
                    ->distinct()
                    ->get();
                //dd($sellers);
                $items = DB::table('order_items')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('order_items.order_id', $payment->order_id)
                    ->select('order_items.*', 'products.*')
                    ->get();

                foreach ($sellers as $seller) {

                    foreach ($items as $item) {
                        // check if the seller exist
                        $seller_exist = Seller_earning::where('seller_id', $seller->seller_id)
                            ->first();

                        if ($item->seller_id == $seller->seller_id) {
                            if ($seller_exist) {
                                // update the upaid_amount
                                DB::table('seller_earnings')
                                    ->where('seller_id', $seller_exist->seller_id)
                                    ->update(['unpaid_amount' => $seller_exist->unpaid_amount + $item->price]);
                            } else {
                                Seller_earning::create([
                                    'seller_id' => $seller->seller_id,
                                    'unpaid_amount' => $item->price
                                ]);
                            }

                            /// decrease qte from the product

                            //dd($item->stock);
                            $U_qte = $item->stock - $item->qte;

                            //// update product
                            DB::table('products')
                                ->where('id', $item->product_id)
                                ->update(['stock' => $U_qte]);
                        }
                    }
                }
                return response()->json([
                    'message' => 'Payment successful!',
                ]);
            } else {
                if ($payment) {
                    DB::table('payments')
                        ->where('id', $payment->id)
                        ->update(['status' => 'failed']);
                }
                return response()->json(['message' => 'Payment failed!'], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function Cancel()
    {
        return response()->json(['message' => 'Payment cancelled.']);
    }

    public function payoutToSeller(Request $request, Seller $seller)
    {
        try {

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();


            if (!$seller) {
                return response()->json([
                    'message' => 'seller not found'
                ], 404);
            }

            $seller_earn = Seller_earning::where('seller_id', $seller->id)->first();
            $payoutData = [
                "sender_batch_header" => [
                    "email_subject" => "You have received a payout!"
                ],
                "items" => [
                    [
                        "recipient_type" => "EMAIL",
                        "amount" => [
                            "value" => $seller_earn->unpaid_amount,
                            "currency" => "USD"
                        ],
                        "receiver" => $seller->paypal,
                        "note" => "Payment for your sales",
                        "sender_item_id" => uniqid()
                    ]
                ]
            ];

            $response = $provider->createBatchPayout($payoutData);

            if (isset($response['batch_header'])) {

                $paid = $seller_earn->paid_amount + $seller_earn->unpaid_amount;

                $seller_earn->update([

                    'unpaid_amount' => 0,
                    'paid_amount' => $paid
                ]);

                return response()->json([
                    'status' => 'success',
                    'batch_id' => $response['batch_header']['payout_batch_id']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payout failed!'
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
