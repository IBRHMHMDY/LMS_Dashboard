<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    public static function getNavigationGroup(): ?string
    {
        return __('Content Management');
    }

    public static function getModelLabel(): string
    {
        return __('Category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Categories');
    }
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Category Details'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->readOnly(),

                        Forms\Components\Select::make('parent_id')
                            ->label(__('Parent Category'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText(__('Leave empty if this is a main category.')),

                        // استبدال حقل النص بحقل رفع صورة ذكي (يستخدم نفس عمود icon في قاعدة البيانات)
                        Forms\Components\FileUpload::make('icon')
                            ->label(__('Thumbnail'))
                            ->image()
                            ->directory('categories/thumbnails')
                            ->helperText(__('Upload a thumbnail image for this category.')),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // 1. تغيير وجهة الضغط على الصف إلى صفحة العرض (View)
            ->recordUrl(
                fn (Category $record): string => CategoryResource::getUrl('view', ['record' => $record])
            )
            // 2. جلب أعداد الكورسات والتصنيفات الفرعية بكفاءة
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount(['courses', 'children']))
            ->columns([
                // عرض الصورة المصغرة في الجدول
                Tables\Columns\ImageColumn::make('icon')
                    ->label(__('Thumbnail'))
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=C&background=random'),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('Parent Category'))
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                // 3. عرض عدد الكورسات داخل التصنيف
                Tables\Columns\TextColumn::make('courses_count')
                    ->label(__('Courses'))
                    ->badge()
                    ->color('success')
                    ->sortable(),

                // 4. عرض عدد التصنيفات الفرعية (تظهر للرئيسية فقط)
                Tables\Columns\TextColumn::make('children_count')
                    ->label(__('Subcategories'))
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state, Category $record) => $record->parent_id === null ? $state : '-')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Status'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('Filter by Parent'))
                    ->relationship('parent', 'name'),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('Active Status')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label(__('View')),
                Tables\Actions\EditAction::make()->label(__('Edit')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('Delete Selected')),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}