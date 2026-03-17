<?php

namespace App\Services\Api;

use App\Enums\CourseStatus;
use App\Models\Course;

class CourseService
{
    // جلب الكورسات المنشورة مع الفلترة والبحث
    public function clonePublishedCourses(array $filters = [], int $perPage = 10)
    {
        $query = Course::with(['instructor', 'category'])
            ->where('status', CourseStatus::PUBLISHED->value);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($perPage);
    }

    // جلب تفاصيل كورس معين بناءً على الـ Slug
    public function getCourseDetails(string $slug)
    {
        return Course::with([
                'instructor', 
                'category', 
                // جلب الوحدات المفعلة والدروس المفعلة مرتبة
                'sections' => fn($q) => $q->where('is_active', true)->orderBy('order'),
                'sections.lessons' => fn($q) => $q->where('is_active', true)->orderBy('order')
            ])
            ->where('status', CourseStatus::PUBLISHED->value)
            ->where('slug', $slug)
            ->firstOrFail();
    }
}