<?php

namespace App\Http\Resources;

use App\Models\User;
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
        $user = User::where('id', $this->user_id)->first();
        return [
            'id' => $this->id,
            'user' => $user,
            'store' => $this->store,
            'phone' => $this->phone,
            'adress' => $this->adress,
            'logo' => $this->logo,
            'status' => $this->status,
            'paypal' => $this->paypal,
        ];
    }
}
