<?php

namespace App\Http\Resources;

use App\Models\Categorie;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategorieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        $product_count = Product::where('category_id', $this->id)->count();
        $parent_category = Categorie::where('id', $this->category_id)->first();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'parent_category' => $parent_category ? $parent_category->name : null,
            'product_count' => $product_count,
            'subcategories' => CategorieResource::collection($this->subcategories),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
