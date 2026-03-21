<?php

namespace App\Models;

use App\Enums\LessonType;
use App\Models\LessonProgress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Lesson extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'section_id',
        'title',
        'slug',
        'content',
        'lesson_type',
        'video_url',
        'duration_in_minutes',
        'order',
        'is_free_preview',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lesson_type' => LessonType::class,
            'is_free_preview' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer',
            'duration_in_minutes' => 'integer',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($lesson) {
            if (is_null($lesson->order)) {
                $maxOrder = static::where('section_id', $lesson->section_id)->max('order');
                $lesson->order = $maxOrder ? $maxOrder + 1 : 1;
            }
        });
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
    
    // تسجيل أنواع الملفات المرفوعة (Media Collections)
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
        $this->addMediaCollection('videos')->singleFile();
    }
}