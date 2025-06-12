<?php

namespace App\Http\Resources;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EarningResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $seller = Seller::findOrFail($this->seller_id);
        return [
            'id' => $this->id,
            'seller_id' => $this->seller_id,
            'seller_name' => $seller->user->name ?? 'Unknown Seller',
            'seller_email' => $seller->user->email ?? 'Unknown Email',
            'unpaid_amount' => $this->unpaid_amount,
            'paid_amount' => $this->paid_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
