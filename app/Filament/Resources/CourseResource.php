<?php

namespace App\Filament\Resources;

use App\Enums\CourseStatus;
use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms\Form; // لن نستخدمه لكن يجب إبقاؤه للـ Interface
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 2;

    // منع الإدارة من إنشاء كورسات جديدة من هذه اللوحة
    public static function canCreate(): bool
    {
        return false;
    }

    // الاستغناء عن form واستخدام infolist لعرض البيانات باحترافية
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Course Overview')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\Grid::make(3)->schema([
                            Infolists\Components\ImageEntry::make('thumbnail')
                                ->hiddenLabel()
                                ->columnSpan(1)
                                ->extraImgAttributes(['class' => 'rounded-lg shadow-sm object-cover']),

                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('title')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('instructor.name')
                                    ->icon('heroicon-o-user')
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('category.name')
                                    ->icon('heroicon-o-tag')
                                    ->color('gray'),
                                Infolists\Components\TextEntry::make('status')
                                    ->badge(),
                            ])->columnSpan(2),
                        ]),
                        
                        Infolists\Components\TextEntry::make('subtitle')
                            ->columnSpanFull()
                            ->color('gray'),
                    ]),

                Infolists\Components\Section::make('Pricing & Level')
                    ->icon('heroicon-o-currency-dollar')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('price')
                            ->money('USD')
                            ->weight('bold')
                            ->color('success'),
                        Infolists\Components\TextEntry::make('discount_price')
                            ->money('USD'),
                        Infolists\Components\TextEntry::make('level')
                            ->badge()
                            ->color('info'),
                    ]),

                Infolists\Components\Section::make('Detailed Content')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->prose()
                            ->html() // لعرض الـ Rich Text بشكل صحيح
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),

                Tables\Columns\TextColumn::make('instructor.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(CourseStatus::class),
                Tables\Filters\SelectFilter::make('instructor_id')
                    ->relationship('instructor', 'name')
                    ->label('Instructor'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                // زر الاعتماد السريع من الجدول
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Course $record) => $record->status === CourseStatus::PENDING)
                    ->action(function (Course $record) {
                        $record->update(['status' => CourseStatus::PUBLISHED]);
                    }),

                // زر الرفض السريع من الجدول
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Course $record) => $record->status === CourseStatus::PENDING)
                    ->action(function (Course $record) {
                        $record->update(['status' => CourseStatus::REJECTED]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'view' => Pages\ViewCourse::route('/{record}'),
        ];
    }
}