<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::where('id', $this->user_id)->first();
        $product = Product::where('id', $this->product_id)->first();
        return [
            'id' => $this->id,
            'user' => new UserResource($user),
            'product' => new ProductResource($product)
        ];
    }
}
