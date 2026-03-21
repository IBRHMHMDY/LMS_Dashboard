<?php

namespace App\Filament\Instructor\Resources\SectionResource\Pages;

use App\Filament\Instructor\Resources\CourseResource;
use App\Filament\Instructor\Resources\SectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSection extends EditRecord
{
    protected static string $resource = SectionResource::class;

    public function getTitle(): string
    {
        return __('Edit Section: ') .$this->record->title;
    }

    // 2. إخفاء الـ Breadcrumbs (المسار) تماماً بإرجاع مصفوفة فارغة
    public function getBreadcrumbs(): array
    {
        $course = $this->record->course;

        return [
            CourseResource::getUrl('index') => __('Courses'),
            $this->getResource()::getUrl('index', ['course_id' => $course->id]) => $course->title,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // توجيه زر الحذف للعودة لجدول الأقسام
            DeleteAction::make()
                ->successRedirectUrl(fn () => $this->getResource()::getUrl('index', ['course_id' => $this->record->course_id])),
        ];
    }

    // العودة لجدول أقسام هذا الكورس بعد التعديل
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', ['course_id' => $this->record->course_id]);
    }
}
