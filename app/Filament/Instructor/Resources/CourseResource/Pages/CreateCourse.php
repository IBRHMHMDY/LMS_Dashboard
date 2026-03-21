<?php

namespace App\Filament\Instructor\Resources\CourseResource\Pages;

use App\Filament\Instructor\Resources\CourseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    public function getTitle(): string
    {
        return __('Create Course:');
    }

    // 2. إخفاء الـ Breadcrumbs (المسار) تماماً بإرجاع مصفوفة فارغة
    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl('index') => __('Courses'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['instructor_id'] = Auth::id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
