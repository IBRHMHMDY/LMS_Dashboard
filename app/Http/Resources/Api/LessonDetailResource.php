<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // التحقق من حالة إكمال الدرس للمستخدم الحالي
        $isCompleted = \App\Models\LessonProgress::where('user_id', $request->user()?->id)
            ->where('lesson_id', $this->id)
            ->whereNotNull('completed_at')
            ->exists();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->lesson_type,
            'duration_in_minutes' => $this->duration_in_minutes,
            
            // جلب الروابط والملفات بناءً على نوع الدرس
            'video_url' => $this->lesson_type === 'video_url' ? $this->video_url : $this->getFirstMediaUrl('videos'),
            'pdf_url' => $this->lesson_type === 'pdf' ? $this->getFirstMediaUrl('attachments') : null,
            'content' => $this->lesson_type === 'text' ? $this->content : null,
            
            'is_completed' => $isCompleted,
            'order' => $this->order,
            'section_id' => $this->section_id,
        ];
    }
}