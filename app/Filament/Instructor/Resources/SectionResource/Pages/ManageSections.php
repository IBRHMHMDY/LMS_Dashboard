<?php

namespace App\Filament\Instructor\Resources\SectionResource\Pages;

use App\Filament\Instructor\Resources\SectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSections extends ManageRecords
{
    protected static string $resource = SectionResource::class;

    // تخصيص الـ Breadcrumbs
    public function getBreadcrumbs(): array
    {
        return [
            url('/instructor') => 'Dashboard',
            'Manage Sections',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Section')
                ->icon('heroicon-o-plus')
                ->modalWidth('md')
                ->modalHeading('Add New Section')
        ];
    }
}