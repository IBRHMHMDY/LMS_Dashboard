<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Enums\CourseStatus;
use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    // إضافة تبويبات لسهولة الفلترة للمدير
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Courses'),
            'pending' => Tab::make('Pending Review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CourseStatus::PENDING))
                ->badge(CourseResource::getEloquentQuery()->where('status', CourseStatus::PENDING)->count())
                ->badgeColor('warning'),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CourseStatus::PUBLISHED)),
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CourseStatus::REJECTED)),
        ];
    }
}