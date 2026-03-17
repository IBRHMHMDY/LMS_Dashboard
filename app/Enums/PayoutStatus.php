<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PAID = 'paid';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Review',
            self::APPROVED => 'Approved (Awaiting Transfer)',
            self::REJECTED => 'Rejected',
            self::PAID => 'Transferred/Paid',
        };
    }
}