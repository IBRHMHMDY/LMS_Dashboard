<?php

namespace App\Filament\Instructor\Resources\LessonResource\Pages;

use App\Filament\Instructor\Resources\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLessons extends ManageRecords
{
    protected static string $resource = LessonResource::class;

    // تخصيص الـ Breadcrumbs
    public function getBreadcrumbs(): array
    {
        return [
            url('/instructor') => 'Dashboard',
            'Manage Lessons',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Lesson')
                ->icon('heroicon-o-plus')
                ->modalWidth('2xl') // نافذة واسعة لتناسب أنواع الدروس المختلفة
                ->modalHeading('Add New Lesson'),
        ];
    }
}