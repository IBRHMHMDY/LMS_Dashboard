<?php

namespace App\Filament\Instructor\Resources\CourseResource\Pages;

use App\Filament\Instructor\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    // 1. تغيير عنوان الصفحة كما هو مطلوب في الملف
    public function getTitle(): string
    {
        return __('Courses');
    }

    // 2. إخفاء الـ Breadcrumbs (المسار) تماماً بإرجاع مصفوفة فارغة
    public function getBreadcrumbs(): array
    {
        return [];
    }

    // 3. زر إنشاء كورس جديد في أعلى الصفحة
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('New Course'))
                ->icon('heroicon-m-plus'),
        ];
    }
}