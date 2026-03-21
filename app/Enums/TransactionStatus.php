<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionStatus: string implements HasLabel, HasColor
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::COMPLETED => __('Completed'),
            self::FAILED => __('Failed'),
            self::REFUNDED => __('Refunded'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDING => StatusColor::WARNING->value,
            self::COMPLETED => StatusColor::SUCCESS->value,
            self::FAILED => StatusColor::DANGER->value,
            self::REFUNDED => StatusColor::GRAY->value,
        };
    }
}