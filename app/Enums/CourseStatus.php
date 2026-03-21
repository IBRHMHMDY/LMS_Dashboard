<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CourseStatus: string implements HasLabel, HasColor
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::PENDING => __('Pending Review'),
            self::PUBLISHED => __('Published'),
            self::REJECTED => __('Rejected'),
        };
    }

    // ربط كل حالة باللون الموحد الخاص بها
    public function getColor(): string | array | null
    {
        return match ($this) {
            self::DRAFT => StatusColor::GRAY->value,
            self::PENDING => StatusColor::WARNING->value,
            self::PUBLISHED => StatusColor::SUCCESS->value,
            self::REJECTED => StatusColor::DANGER->value,
        };
    }
}