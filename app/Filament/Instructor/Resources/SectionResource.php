<?php

namespace App\Filament\Instructor\Resources;

use App\Filament\Instructor\Resources\SectionResource\Pages;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // 1. إخفاء الأقسام من القائمة الجانبية (Navigation Menu)
    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('Section');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Sections');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Section Details'))
                    ->schema([
                        // حقل مخفي لالتقاط رقم الكورس من الرابط برمجياً
                        Forms\Components\Hidden::make('course_id')
                            ->default(request()->query('course_id'))
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label(__('Section Name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('order')
                            ->label(__('Order'))
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn (Section $record): string => LessonResource::getUrl('index', [
                    'section_id' => $record->id, 
                    'course_id' => request()->query('course_id')
                ])
            )
            // 2. تصفية الجدول لعرض أقسام الكورس المحدد فقط وجلب عدد الدروس
            ->modifyQueryUsing(function (Builder $query) {
                $courseId = request()->query('course_id');
                if ($courseId) {
                    $query->where('course_id', $courseId);
                }
                $query->withCount('lessons');
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Section Name'))
                    ->searchable()
                    ->weight('bold'),

                // 3. عرض عدد الدروس
                Tables\Columns\TextColumn::make('lessons_count')
                    ->label(__('Number of Lessons'))
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-document-text')
                    ->sortable(),
            ])
            ->filters([
                // يمكن إضافة فلاتر هنا مستقبلاً
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('Edit'))
                    ->color('gray')
                    // تمرير الـ Course ID أثناء التعديل للحفاظ على المسار
                    ->url(fn (Section $record): string => SectionResource::getUrl('edit', ['record' => $record, 'course_id' => request()->query('course_id')])),
                
                // 4. زر التوجه إلى صفحة الدروس الخاصة بهذا القسم
                Tables\Actions\Action::make('manage_lessons')
                    ->label(__('Manage Lessons'))
                    ->icon('heroicon-m-video-camera')
                    ->button()
                    ->color('primary')
                    ->url(fn (Section $record): string => LessonResource::getUrl('index', ['section_id' => $record->id, 'course_id' => request()->query('course_id')])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // 5. تسجيل الصفحات الجديدة التي أنشأناها في الخطوة الأولى
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}