<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = DB::table('users')->where('id', $this->user_id)->first();
        $items = DB::table('order_items')->where('order_id', $this->id)->get();
        return [
            'id' => $this->id,
            'user' => new UserResource($user),
            'adress_delivery' => $this->adress_delivery,
            'total' => $this->total,
            'status' => $this->status,
            'is_done' => $this->is_done,
            'items' => OrderItemResource::collection($items),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
