<?php

namespace App\Filament\Instructor\Resources;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Filament\Instructor\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'My Courses';
    protected static ?string $modelLabel = 'Course';
    protected static ?int $navigationSort = 1;

    // قصر البيانات على كورسات المدرب الحالي فقط
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('instructor_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Course Details')
                    ->tabs([
                        // تبويب المعلومات الأساسية
                        Tab::make('General Information')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'edit' ? $set('slug', Str::slug($state) . '-' . uniqid()) : null)
                                    ->maxLength(255),
                                
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->readOnly(),

                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Textarea::make('subtitle')
                                    ->maxLength(500)
                                    ->columnSpanFull(),

                                RichEditor::make('description')
                                    ->required()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        // تبويب التسعير والمستوى
                        Tab::make('Pricing & Status')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                TextInput::make('price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0.00)
                                    ->required(),

                                TextInput::make('discount_price')
                                    ->numeric()
                                    ->prefix('$'),

                                Select::make('level')
                                    ->options(CourseLevel::class)
                                    ->default(CourseLevel::BEGINNER)
                                    ->required(),

                                Select::make('status')
                                    ->options(CourseStatus::class)
                                    ->default(CourseStatus::DRAFT)
                                    ->required(),
                            ])->columns(2),

                        // تبويب الوسائط (الصور والفيديو)
                        Tab::make('Media')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                FileUpload::make('thumbnail')
                                    ->image()
                                    ->directory('course-thumbnails')
                                    ->columnSpanFull(),

                                TextInput::make('promo_video_url')
                                    ->url()
                                    ->label('Promo Video URL (Youtube/Vimeo)')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3, 
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    
                    // 1. الصورة العلوية (استخدام الـ View المخصص لكسر كل قيود Filament)
                    Tables\Columns\ViewColumn::make('thumbnail')
                        ->view('filament.instructor.components.course-thumbnail'),
                    
                    // 2. محتوى البطاقة
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('title')
                            ->weight('bold')
                            ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                            ->searchable()
                            ->limit(40),

                        Tables\Columns\TextColumn::make('category.name')
                            ->color('gray')
                            ->icon('heroicon-m-tag'),

                        Tables\Columns\Layout\Split::make([
                            Tables\Columns\TextColumn::make('price')
                                ->money('USD') 
                                ->weight('bold')
                                ->color('success'),
                            
                            Tables\Columns\TextColumn::make('status')
                                ->badge(),
                        ])->extraAttributes(['style' => 'margin-top: 1rem; align-items: center;']),
                    ])->space(2)->extraAttributes(['style' => 'padding: 1.25rem; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between;']),
                ])
                ->space(0)
                // الغلاف الخارجي للبطاقة
                ->extraAttributes([
                    'style' => 'background-color: var(--fi-bg-color, white); border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid var(--fi-border-color, #e5e7eb); overflow: hidden; display: flex; flex-direction: column; height: 100%; padding: 0;'
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(\App\Enums\CourseStatus::class),
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name')->label('Category'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->color('primary')
                    ->extraAttributes(['style' => 'margin: 1rem; width: calc(100% - 2rem); display: flex; justify-content: center;']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
            // تم إزالة صفحة create حسب طلبك
        ];
    }
}