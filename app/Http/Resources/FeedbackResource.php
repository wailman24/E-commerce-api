<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::findOrFail($this->user_id);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($user),
            'email' => $this->email,
            'message' => $this->message,
            'created_at' => $this->created_at
        ];
    }
}
