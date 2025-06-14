<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Order_item;
use App\Models\Seller;
use Illuminate\Http\Request;
use App\Models\Seller_earning;
use App\Models\SellerPayout;
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

            $order = Order::findOrFail($request->order_id);

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => $order->total
                        ]
                    ]
                ],
                "application_context" => [
                    "return_url" => route('payment.success'),
                    "cancel_url" => route('payment.cancel')
                ]
            ]);
            //dd($response);

            if (isset($response['id']) && $response['id'] != null) {
                $paypalToken = $response['id']; // Save PayPal Order ID


                // Save the payment with the PayPal token
                Payment::create([
                    'order_id' => $order->id,
                    'methode' => 'paypal',
                    'status' => 'pending',
                    'paypal_order_id' => $paypalToken,
                ]);

                return response()->json([
                    'status' => 'success',
                    'token' => $paypalToken,
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

    public function success(Request $request)
    {
        try {
            $paypalToken = $request->query('token'); // The PayPal Order ID

            if (!$paypalToken) {
                return response()->json([
                    'status' => false,
                    'message' => 'Missing PayPal token.'
                ], 400);
            }

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $response = $provider->capturePaymentOrder($paypalToken);

            $payment = Payment::where('paypal_order_id', $paypalToken)->first();

            if (!$payment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment not found.'
                ], 404);
            }

            if ($response['status'] === 'COMPLETED') {
                // 1. Update payment status
                $payment->status = 'completed';
                $payment->save();

                // 2. Mark order as done
                $order = Order::find($payment->order_id);
                $order->is_done = true;
                $order->save();

                // 3. Calculate seller earnings
                $sellers = DB::table('orders')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('orders.id', $payment->order_id)
                    ->select('products.seller_id')
                    ->distinct()
                    ->get();

                $items = DB::table('order_items')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('order_items.order_id', $payment->order_id)
                    ->select('order_items.*', 'products.*')
                    ->get();

                foreach ($sellers as $seller) {
                    foreach ($items as $item) {
                        if ($item->seller_id == $seller->seller_id) {
                            $sellerEarning = Seller_earning::firstOrCreate(
                                ['seller_id' => $seller->seller_id],
                                ['unpaid_amount' => 0]
                            );

                            $sellerEarning->unpaid_amount += $item->price;
                            $sellerEarning->save();

                            $updatedStock = $item->stock - $item->qte;
                            DB::table('products')
                                ->where('id', $item->product_id)
                                ->update(['stock' => $updatedStock]);
                        }
                    }
                }

                $token = $request->query('token');
                $payerId = $request->query('PayerID');

                return redirect()->away("http://localhost:5173/payment/success?token=$token&PayerID=$payerId");
            } else {
                $payment->status = 'failed';
                $payment->save();

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
        return redirect('http://localhost:5173/payment/cancel');
    }

    public function payoutToSeller(Request $request, $id)
    {
        try {

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $seller = Seller::where('id', $id)->first();
            if (!$seller) {
                return response()->json([
                    'message' => 'seller not found'
                ], 404);
            }

            $seller_earn = Seller_earning::where('seller_id', $seller->id)->first();
            if (!$seller_earn) {
                return response()->json([
                    'message' => 'Earnings not found for this seller'
                ], 404);
            }
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

                SellerPayout::create([
                    'seller_id' => $seller->id,
                    'amount_paid' => $seller_earn->unpaid_amount,
                    'batch_id' => $response['batch_header']['payout_batch_id'],
                    'paid_at' => now(),
                ]);

                $paid = $seller_earn->paid_amount + $seller_earn->unpaid_amount;

                $seller_earn->update([
                    'unpaid_amount' => 0,
                    'paid_amount' => $paid
                ]);

                // ✅ Save to payouts history

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

    public function paymentondelivery(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $order = Order::findOrFail($id);

            if ($order->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not allowed to pay for this order.'
                ], 403);
            }

            // Update order status to completed
            $order->is_done = true;
            $order->save();

            // Mark payment as completed
            Payment::create([
                'order_id' => $order->id,
                'methode' => 'cash_on_delivery',
                'status' => 'completed',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment completed successfully.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
