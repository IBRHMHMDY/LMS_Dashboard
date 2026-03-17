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
use Illuminate\Support\Facades\Auth;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Course Sections';
    protected static ?string $modelLabel = 'Section';
    protected static ?int $navigationSort = 2; // يظهر تحت الكورسات

    // قصر عرض الوحدات على الكورسات التي يمتلكها المدرب الحالي فقط
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('course', function (Builder $query) {
            $query->where('instructor_id', Auth::id());
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // اختيار الكورس (تظهر كورسات هذا المدرب فقط)
                Forms\Components\Select::make('course_id')
                    ->relationship(
                        name: 'course',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $query) => $query->where('instructor_id', Auth::id())
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Used for sorting. You can also drag and drop in the table.'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->sortable()
                    ->searchable()
                    ->description(fn (Section $record): string => 'Section: ' . $record->title)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->hidden(), // أخفيناه لأننا عرضناه كـ description تحت اسم الكورس لترتيب أفضل

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status'),

                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // فلتر لعرض وحدات كورس معين
                Tables\Filters\SelectFilter::make('course_id')
                    ->label('Filter by Course')
                    ->relationship(
                        name: 'course',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $query) => $query->where('instructor_id', Auth::id())
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('md'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order') // تفعيل خاصية السحب والإفلات لترتيب الوحدات
            ->defaultSort('course_id', 'asc') // الترتيب الافتراضي
            ->defaultSort('order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            // استخدام ManageRecords يعني أن كل العمليات ستتم في نفس الصفحة عبر Modals
            'index' => Pages\ManageSections::route('/'),
        ];
    }
}