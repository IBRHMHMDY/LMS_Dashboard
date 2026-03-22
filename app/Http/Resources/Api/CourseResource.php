<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'thumbnail' => $this->thumbnail ? url('storage/' . $this->thumbnail) : null,
            'price' => (float) $this->price,
            'discount_price' => $this->discount_price ? (float) $this->discount_price : null,
            'level' => $this->level,
            
            // بيانات التقييم والمفضلة (يتم حسابها في الـ Controller)
            'average_rating' => (float) round($this->reviews_avg_rating ?? 0, 1),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'is_in_wishlist' => (bool) ($this->wishlisted_by_user_exists ?? false),

            'instructor' => [
                'id' => $this->instructor->id ?? null,
                'name' => $this->instructor->name ?? null,
                'avatar' => $this->instructor->avatar ? url('storage/' . $this->instructor->avatar) : null,
            ],
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
            ],
        ];
    }
}