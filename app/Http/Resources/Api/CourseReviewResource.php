<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at->toIso8601String(),
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'avatar' => isset($this->user->avatar) ? url('storage/' . $this->user->avatar) : null,
            ]
        ];
    }
}