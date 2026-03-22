<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'instructor_id',
        'course_id',
        'transaction_number',
        'amount',
        'platform_commission',
        'instructor_earning',
        'status',
        'payment_method',
        'payment_gateway',
        'gateway_transaction_id',
        'receipt_data',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'platform_commission' => 'decimal:2',
            'instructor_commission' => 'decimal:2',
            'status' => TransactionStatus::class,
        ];
    }

    // الطالب (المشتري)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // المدرب (صاحب الكورس)
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}