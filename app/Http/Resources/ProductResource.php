<?php

namespace App\Http\Resources;

use App\Models\Image;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images = Image::where('product_id', $this->id)->get();
        $rating = Review::where('product_id', $this->id)->avg('rating');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'about' => $this->about,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_valid' => $this->is_valid,
            'seller_id' => $this->seller_id,
            'images' => ImageResource::collection($images),
            'rating' => $rating
        ];
    }
}
