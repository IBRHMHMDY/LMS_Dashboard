<?php

namespace App\Filament\Resources\InstructorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoursesRelationManager extends RelationManager
{
    // اسم العلاقة في موديل User
    protected static string $relationship = 'coursesAsInstructor';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Instructor Courses');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label(__('Cover'))
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('Course Title'))
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Category')),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price (EGP)'))
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
            ])
            ->actions([
                // زر اختصار يذهب بالمدير لصفحة تفاصيل الكورس في CourseResource
                Tables\Actions\Action::make('view_course')
                    ->label(__('View Course'))
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record): string => \App\Filament\Resources\CourseResource::getUrl('view', ['record' => $record]))
            ]);
    }
}