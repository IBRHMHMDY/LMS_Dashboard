<?php

namespace App\Filament\Instructor\Resources\LessonResource\Pages;

use App\Filament\Instructor\Resources\LessonResource;
use App\Filament\Instructor\Resources\SectionResource;
use App\Filament\Instructor\Resources\CourseResource;
use App\Models\Section;
use App\Models\Course;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessons extends ListRecords
{
    protected static string $resource = LessonResource::class;

    // 1. عنوان الصفحة الديناميكي (اسم القسم)
    public function getTitle(): string
    {
        $sectionId = request()->query('section_id');
        if ($sectionId) {
            $section = Section::find($sectionId);
            return $section ? __('Section: ') . $section->title : __('');
        }
        return __('');
    }

    // 2. الـ Breadcrumbs المخصص (الكورسات -> اسم الكورس)
    public function getBreadcrumbs(): array
    {
        $courseId = request()->query('course_id');
        $course = Course::find($courseId);

        return [
            CourseResource::getUrl('index') => __('Courses'),
            $course ? SectionResource::getUrl('index', ['course_id' => $courseId]) : '#' => $course ? $course->title : __('Sections'),
        ];
    }

    // 3. زر إنشاء درس جديد مع تمرير الروابط
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('New Lesson'))
                ->icon('heroicon-m-plus')
                ->url(fn (): string => LessonResource::getUrl('create', [
                    'section_id' => request()->query('section_id'),
                    'course_id' => request()->query('course_id')
                ])),
        ];
    }
}