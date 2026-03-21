<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LessonType: string implements HasLabel, HasColor
{
    case VIDEO_URL = 'video_url';
    case VIDEO_UPLOAD = 'video_upload';
    case TEXT = 'text';
    case PDF = 'pdf';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::VIDEO_URL => __('Video URL'),
            self::VIDEO_UPLOAD => __('Video Upload'),
            self::TEXT => __('Text Content'),
            self::PDF => __('PDF Document'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::VIDEO_URL => StatusColor::INFO->value,
            self::VIDEO_UPLOAD => StatusColor::WARNING->value,
            self::TEXT => StatusColor::SUCCESS->value,
            self::PDF => StatusColor::DANGER->value,
        };
    }
}