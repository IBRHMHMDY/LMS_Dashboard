<?php

namespace App\Filament\Instructor\Resources;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Filament\Instructor\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    // استخدام دالة الترجمة لاسم الـ Resource في القائمة الجانبية
    public static function getModelLabel(): string
    {
        return __('Course');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Courses');
    }

    public static function getNavigationLabel(): string
    {
        return __('Courses');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make(__('Course Details'))
                    ->tabs([
                        // Tab 1: Basic Information
                        Forms\Components\Tabs\Tab::make(__('Basic Information'))
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('Course Title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                
                                Forms\Components\TextInput::make('slug')
                                    ->label(__('Slug'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->readOnly()
                                    ->helperText(__('Auto-generated from title.')),

                                Forms\Components\TextInput::make('subtitle')
                                    ->label(__('Subtitle'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('category_id')
                                    ->label(__('Category'))
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Select::make('level')
                                    ->label(__('Level'))
                                    ->options(CourseLevel::class)
                                    ->required(),
                            ])->columns(2),

                        // Tab 2: Media & Description
                        Forms\Components\Tabs\Tab::make(__('Media & Description'))
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail')
                                    ->label(__('Course Thumbnail'))
                                    ->image()
                                    ->directory('courses/thumbnails')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('promo_video_url')
                                    ->label(__('Promotional Video URL'))
                                    ->url()
                                    ->placeholder('https://youtube.com/...')
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('description')
                                    ->label(__('Course Description'))
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        // Tab 3: Curriculum Extras
                        Forms\Components\Tabs\Tab::make(__('Course Outcomes'))
                            ->icon('heroicon-m-academic-cap')
                            ->schema([
                                Forms\Components\Repeater::make('what_you_will_learn')
                                    ->label(__('What You Will Learn'))
                                    ->simple(
                                        Forms\Components\TextInput::make('learning_point')
                                            ->required()
                                            ->placeholder(__('e.g. Build RESTful APIs with Laravel'))
                                    )
                                    ->addActionLabel(__('Add Learning Point'))
                                    ->columnSpanFull(),

                                Forms\Components\Repeater::make('requirements')
                                    ->label(__('Requirements / Prerequisites'))
                                    ->simple(
                                        Forms\Components\TextInput::make('requirement')
                                            ->required()
                                            ->placeholder(__('e.g. Basic understanding of PHP'))
                                    )
                                    ->addActionLabel(__('Add Requirement'))
                                    ->columnSpanFull(),
                            ]),

                        // Tab 4: Pricing & Status
                        Forms\Components\Tabs\Tab::make(__('Pricing & Status'))
                            ->icon('heroicon-m-currency-dollar')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label(__('Price (EGP)'))
                                    ->numeric()
                                    ->prefix(__('EGP'))
                                    ->helperText(__('Leave empty or set to 0 to make it Free.')),

                                Forms\Components\TextInput::make('discount_price')
                                    ->label(__('Discount Price (EGP)'))
                                    ->numeric()
                                    ->prefix(__('EGP'))
                                    ->lt('price')
                                    ->helperText(__('Must be lower than the original price.')),

                                Forms\Components\Select::make('status')
                                    ->label(__('Status'))
                                    ->options(CourseStatus::class)
                                    ->default(CourseStatus::PENDING->value)
                                    ->required(),
                            ])->columns(2),
                    ])
                    ->columnSpanFull()
                    ->activeTab(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn (Course $record): string => SectionResource::getUrl('index', ['course_id' => $record->id])
            )
            // جلب عدد المشتركين مع الاستعلام لتقليل الضغط على قاعدة البيانات
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('enrollments'))
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label(__('Cover'))
                    ->size(50)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover'])
                    ->defaultImageUrl('https://placehold.co/600x400/f3f4f6/9ca3af?text=' . urlencode(__('Cover'))),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('Course Name & Category'))
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Course $record): string => $record->category?->name ?? __('Uncategorized'))
                    ->wrap(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->badge()
                    ->color(fn ($state) => $state == 0 || is_null($state) ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state == 0 || is_null($state) ? __('Free') : number_format($state, 2) . ' ' . __('EGP'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),

                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label(__('Students'))
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-users')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(CourseStatus::class),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label(__('Category')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('Edit'))
                    ->button()
                    ->color('gray'),
                
                // زر التوجه إلى صفحة الأقسام
                Tables\Actions\Action::make('manage_sections')
                    ->label(__('Manage Sections'))
                    ->icon('heroicon-m-rectangle-stack')
                    ->button()
                    ->color('primary')
                    ->url(fn (Course $record): string => SectionResource::getUrl('index', ['course_id' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}