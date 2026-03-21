<?php

namespace App\Filament\Instructor\Resources\SectionResource\Pages;

use App\Filament\Instructor\Resources\CourseResource;
use App\Filament\Instructor\Resources\SectionResource;
use App\Models\Course;
use Filament\Resources\Pages\CreateRecord;

class CreateSection extends CreateRecord
{
    protected static string $resource = SectionResource::class;

    public function getTitle(): string
    {
        return __('Create Section');
    }

    // 2. إخفاء الـ Breadcrumbs (المسار) تماماً بإرجاع مصفوفة فارغة
    public function getBreadcrumbs(): array
    {
        $courseId = request()->query('course_id');
        $course = Course::find($courseId);

        return [
            CourseResource::getUrl('index') => __('Courses'),
            $course ? $this->getResource()::getUrl('index', ['course_id' => $courseId]) : '#' => $course ? $course->title : __('Sections'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', ['course_id' => $this->record->course_id]);
    }
}
