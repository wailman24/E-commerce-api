<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = DB::table('products')->where('id', $this->product_id)->first();
        $order = DB::table('orders')->where('id', $this->order_id)->first();


        return [
            'id' => $this->id,
            'product' => new ProductResource($product),
            'adress_delivery' => $order->adress_delivery ?? 'Unknown Address',
            'order_id' => $this->order_id,
            'qte' => $this->qte,
            'price' => $this->price,
            'status' => $this->status
        ];
    }
}
