<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class CourseDetailResource extends CourseResource
{
    public function toArray(Request $request): array
    {
        // دمج البيانات الأساسية مع التفاصيل الإضافية (الوصف والمحتوى)
        return array_merge(parent::toArray($request), [
            'description' => $this->description,
            'promo_video_url' => $this->promo_video_url,
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'created_at' => $this->created_at->toIso8601String(),
        ]);
    }
}