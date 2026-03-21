<?php

namespace App\Filament\Resources\InstructorResource\Pages;

use App\Filament\Resources\InstructorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInstructor extends ViewRecord
{
    protected static string $resource = InstructorResource::class;

    public function getTitle(): string
    {
        return $this->record->name; // سيكون اسم المدرب هو عنوان الصفحة
    }

    protected function getHeaderActions(): array
    {
        return []; // إخفاء زر التعديل لمنع الإدارة من التعديل
    }
}