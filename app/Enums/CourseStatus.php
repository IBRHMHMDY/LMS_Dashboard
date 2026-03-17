<?php

namespace App\Enums;

enum CourseStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending'; // بانتظار موافقة الإدارة
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Approval',
            self::PUBLISHED => 'Published',
            self::REJECTED => 'Rejected',
        };
    }
}