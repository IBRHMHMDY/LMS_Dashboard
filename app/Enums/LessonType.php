<?php

namespace App\Enums;

enum LessonType: string
{
    case VIDEO_URL = 'video_url'; // رابط خارجي مثل Youtube / Vimeo
    case VIDEO_UPLOAD = 'video_upload'; // فيديو مرفوع على السيرفر/S3
    case TEXT = 'text'; // مقال أو محتوى نصي
    case PDF = 'pdf'; // ملف PDF يعرض داخل المنصة

    public function label(): string
    {
        return match($this) {
            self::VIDEO_URL => 'Video URL',
            self::VIDEO_UPLOAD => 'Uploaded Video',
            self::TEXT => 'Text Content',
            self::PDF => 'PDF Document',
        };
    }
}