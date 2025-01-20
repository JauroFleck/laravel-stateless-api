<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\PersonalAccessToken;

class TokenResource extends JsonResource
{
    /**
     * Initialize the instance with the given resource.
     *
     * @param PersonalAccessToken $resource The personal access token resource instance.
     */
    public function __construct(PersonalAccessToken $resource) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'last_used_at' => $this->resource->last_used_at,
        ];
    }
}
