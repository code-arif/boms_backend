<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'plan' => $this->plan,
            'users_count' => $this->users_count ?? null,
            'settings' => $this->settings,
            'created_at'  => $this->created_at->toISOString(),
        ];
    }
}
