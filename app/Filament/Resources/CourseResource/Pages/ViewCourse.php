<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Enums\CourseStatus;
use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve Course')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Course')
                ->modalDescription('Are you sure you want to approve and publish this course?')
                ->visible(fn () => $this->record->status !== CourseStatus::PUBLISHED)
                ->action(function () {
                    $this->record->update(['status' => CourseStatus::PUBLISHED]);
                    $this->refreshFormData(['status']);
                }),

            Actions\Action::make('reject')
                ->label('Reject Course')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Course')
                ->modalDescription('Are you sure you want to reject this course? The instructor will need to update it.')
                ->visible(fn () => $this->record->status !== CourseStatus::REJECTED)
                ->action(function () {
                    $this->record->update(['status' => CourseStatus::REJECTED]);
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}