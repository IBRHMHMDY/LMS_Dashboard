<?php

namespace App\Filament\Instructor\Resources\SectionResource\Pages;

use App\Filament\Instructor\Resources\SectionResource;
use App\Filament\Instructor\Resources\CourseResource;
use App\Models\Course;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSections extends ListRecords
{
    protected static string $resource = SectionResource::class;

    // 1. عنوان الصفحة الديناميكي (اسم الكورس)
    public function getTitle(): string
    {
        $courseId = request()->query('course_id');
        if ($courseId) {
            $course = Course::find($courseId);
            return $course ? __('Course: ') . $course->title : __('');
        }
        return __('');
    }

    // 2. الـ Breadcrumbs المخصص (ينقلك لصفحة الكورسات)
    public function getBreadcrumbs(): array
    {
        return [
            CourseResource::getUrl('index') => __('Courses'),
        ];
    }

    // 3. زر "قسم جديد"
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('New Section'))
                ->icon('heroicon-m-plus')
                // تمرير رقم الكورس لصفحة الإنشاء
                ->url(fn (): string => SectionResource::getUrl('create', ['course_id' => request()->query('course_id')])),
        ];
    }
}