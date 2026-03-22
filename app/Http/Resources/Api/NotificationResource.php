<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => class_basename($this->type), // لتسهيل قراءة نوع الإشعار في الموبايل
            'data' => $this->data, // محتوى الإشعار (العنوان، الرسالة، الخ)
            'read_at' => $this->read_at ? $this->read_at->toIso8601String() : null,
            'is_read' => $this->read_at !== null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}