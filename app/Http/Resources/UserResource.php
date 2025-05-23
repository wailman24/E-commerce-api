<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = DB::table('roles')->where('id', $this->role_id)->first();
        $seller = DB::table('sellers')->where('user_id', $this->id)->first();
        if ($seller) {
            $seller = new SellerResource($seller);
        } else {
            $seller = null;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $role->name,
            'role_id' => $this->role_id,
            'seller' => $seller
        ];
    }
}
