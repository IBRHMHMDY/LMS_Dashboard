<?php

namespace App\Models;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'instructor_id',
        'category_id',
        'title',
        'slug',
        'subtitle',
        'description',
        'price',
        'discount_price',
        'level',
        'status',
        'thumbnail',
        'promo_video_url',
        'requirements',
        'what_you_will_learn',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'level' => CourseLevel::class,
            'status' => CourseStatus::class,
            'requirements' => 'array',
            'what_you_will_learn' => 'array',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}