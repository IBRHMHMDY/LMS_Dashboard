<?php

namespace App\Filament\Instructor\Resources\CourseResource\Pages;

use App\Filament\Instructor\Resources\CourseResource;
use App\Models\Course;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    // تخصيص الـ Breadcrumbs
    public function getBreadcrumbs(): array
    {
        return [
            url('/instructor') => 'Dashboard',
            'My Courses',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // زر إضافة كورس جديد يفتح كـ Modal
            CreateAction::make()
                ->label('New Course')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Start a New Course')
                ->modalDescription('Enter the basic details to get started. You can add the full content later.')
                ->modalWidth('md')
                ->model(Course::class)
                ->form([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                        
                    TextInput::make('price')
                        ->numeric()
                        ->prefix('$')
                        ->default(0.00)
                        ->required(),
                    RichEditor::make('description')
                                    ->required()
                                    ->columnSpanFull(),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    // تعيين المدرب الحالي آلياً وإنشاء الـ slug
                    $data['instructor_id'] = Auth::id();
                    $data['slug'] = Str::slug($data['title']) . '-' . uniqid();
                    return $data;
                })
                // التوجيه إلى صفحة التعديل فور إنشاء الكورس
                ->successRedirectUrl(fn (Course $record): string => CourseResource::getUrl('edit', ['record' => $record])),
        ];
    }
}