<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->lesson_type,
            'duration_in_minutes' => $this->duration_in_minutes,
            'is_free_preview' => (bool) $this->is_free_preview,
            'order' => $this->order,
            // إظهار المحتوى فقط إذا كان الدرس مجانياً (Preview)
            'video_url' => $this->when($this->is_free_preview, $this->video_url),
            'content' => $this->when($this->is_free_preview, $this->content),
        ];
    }
}