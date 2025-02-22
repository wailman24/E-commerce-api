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
        
        return [
            'id' => $this->id,
            'user' => $user,
            'adress_delivery' => $this->adress_delivery,
            'total' => $this->total,
            'status' => $this->status,
            'cart' => $this->is_done,
        ];
    }
}
