<?php

namespace App\Filament\Instructor\Resources\LessonResource\Pages;

use App\Enums\LessonType;
use App\Filament\Instructor\Resources\CourseResource;
use App\Filament\Instructor\Resources\LessonResource;
use App\Filament\Instructor\Resources\SectionResource;
use App\Models\Course;
use App\Models\Section;
use Filament\Resources\Pages\CreateRecord;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;

    public function getTitle(): string
    {
        return __('Create Lesson');
    }

    public function getBreadcrumbs(): array
    {
        $courseId = request()->query('course_id');
        $sectionId = request()->query('section_id');
        $course = Course::find($courseId);
        $section = Section::find($sectionId);

        return [
            CourseResource::getUrl('index') => __('Courses'),
            $course ? SectionResource::getUrl('index', ['course_id' => $courseId]) : '#' => $course ? $course->title : __('Sections'),
            $section ? $this->getResource()::getUrl('index', ['section_id' => $sectionId, 'course_id' => $courseId]) : '#' => $section ? $section->title : __('Lessons'),
        ];
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['lesson_type'] === LessonType::VIDEO_URL->value) {
            $data['video_url'] = $data['video_url'] ?? null;
        } elseif ($data['lesson_type'] === LessonType::VIDEO_UPLOAD->value) {
            $data['video_url'] = $data['video_upload_file'] ?? null;
        } elseif ($data['lesson_type'] === LessonType::PDF->value) {
            $data['video_url'] = $data['pdf_upload_file'] ?? null; // حفظ مسار الـ PDF
        } else {
            $data['video_url'] = null;
        }

        // مسح الحقول الوهمية
        unset($data['video_url_link'], $data['video_upload_file'], $data['pdf_upload_file']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'section_id' => $this->record->section_id,
            'course_id' => request()->query('course_id') ?? $this->record->section->course_id
        ]);
    }
}
