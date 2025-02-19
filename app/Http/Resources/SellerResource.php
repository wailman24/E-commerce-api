<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        return [
            'id' => $this->id,
            'user' => $user,
            'store' => $this->store,
            'phone' => $this->phone,
            'adress' => $this->adress,
            'status' => $this->status
        ];
    }
}
