<?php

namespace App\Filament\Resources\InstructorResource\Pages;

use App\Filament\Resources\InstructorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInstructor extends CreateRecord
{
    protected static string $resource = InstructorResource::class;

    public function getTitle(): string
    {
        return __('New Instructor');
    }

    // بمجرد إنشاء المستخدم، نقوم بإعطائه دور (مدرب) تلقائياً
    protected function afterCreate(): void
    {
        $this->record->assignRole('instructor');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}