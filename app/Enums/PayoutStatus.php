<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PayoutStatus: string implements HasLabel, HasColor
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PAID = 'paid';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::APPROVED => __('Approved'),
            self::REJECTED => __('Rejected'),
            self::PAID => __('Paid'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDING => StatusColor::WARNING->value,
            self::APPROVED => StatusColor::PRIMARY->value, // لون مميز للموافقة المبدئية
            self::REJECTED => StatusColor::DANGER->value,
            self::PAID => StatusColor::SUCCESS->value,     // تم الدفع بنجاح
        };
    }
}