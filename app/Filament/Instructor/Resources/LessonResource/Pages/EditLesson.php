<?php

namespace App\Filament\Instructor\Resources\LessonResource\Pages;

use App\Filament\Instructor\Resources\CourseResource;
use App\Filament\Instructor\Resources\LessonResource;
use App\Filament\Instructor\Resources\SectionResource;
use App\Enums\LessonType;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    public function getTitle(): string
    {
        return __('Edit Lesson: ') .$this->record->title;
    }

    // 2. الـ Breadcrumbs المخصص (الكورسات -> اسم الكورس)
    public function getBreadcrumbs(): array
    {
        $section = $this->record->section;
        $course = $section->course;

        return [
            CourseResource::getUrl('index') => __('Courses'),
            SectionResource::getUrl('index', ['course_id' => $course->id]) => $course->title,
            $this->getResource()::getUrl('index', ['section_id' => $section->id, 'course_id' => $course->id]) => $section->title,
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($data['lesson_type'] === LessonType::VIDEO_URL->value) {
            $data['video_url_link'] = $data['video_url'];
        } elseif ($data['lesson_type'] === LessonType::VIDEO_UPLOAD->value) {
            $data['video_upload_file'] = $data['video_url'];
        } elseif ($data['lesson_type'] === LessonType::PDF->value) {
            $data['pdf_upload_file'] = $data['video_url']; // عرض الـ PDF المحفوظ
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['lesson_type'] === LessonType::VIDEO_URL->value) {
            $data['video_url'] = $data['video_url_link'] ?? null;
        } elseif ($data['lesson_type'] === LessonType::VIDEO_UPLOAD->value) {
            $data['video_url'] = $data['video_upload_file'] ?? null;
        } elseif ($data['lesson_type'] === LessonType::PDF->value) {
            $data['video_url'] = $data['pdf_upload_file'] ?? null; // حفظ التعديل
        } else {
            $data['video_url'] = null;
        }

        unset($data['video_url_link'], $data['video_upload_file'], $data['pdf_upload_file']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            // توجيه زر الحذف للعودة لجدول الدروس
            Actions\DeleteAction::make()
                ->successRedirectUrl(fn () => $this->getResource()::getUrl('index', [
                    'section_id' => $this->record->section_id,
                    'course_id' => request()->query('course_id') ?? $this->record->section->course_id
                ])),
        ];
    }

    // العودة لجدول دروس هذا القسم بعد التعديل
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'section_id' => $this->record->section_id,
            'course_id' => request()->query('course_id') ?? $this->record->section->course_id
        ]);
    }
}
