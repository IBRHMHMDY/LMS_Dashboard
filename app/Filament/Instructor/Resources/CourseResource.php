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
                'xl' => 3, // عرض 3 بطاقات في الشاشات الكبيرة
            ])
            ->columns([
                // بناء شكل البطاقة (Card Layout) بدلاً من الصفوف التقليدية
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('thumbnail')
                        ->height('200px')
                        ->width('100%')
                        ->extraImgAttributes(['class' => 'object-cover rounded-t-xl'])
                        ->defaultImageUrl(url('https://ui-avatars.com/api/?name=Course&background=random&size=400')),
                    
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('title')
                            ->weight('bold')
                            ->size('lg')
                            ->searchable(),

                        Tables\Columns\TextColumn::make('category.name')
                            ->color('gray')
                            ->icon('heroicon-o-tag'),

                        Tables\Columns\Layout\Split::make([
                            Tables\Columns\TextColumn::make('price')
                                ->money('EGP')
                                ->weight('bold')
                                ->color('success'),
                            
                            Tables\Columns\TextColumn::make('status')
                                ->badge(),
                        ])->extraAttributes(['class' => 'mt-4']),
                    ])->space(2)->extraAttributes(['class' => 'p-4 border-t border-gray-100 dark:border-gray-800']),
                ])->space(0), // مسافة صفر لدمج الصورة مع المحتوى بشكل احترافي
            ])
            ->filters([
                // فلاتر البحث
                Tables\Filters\SelectFilter::make('status')->options(CourseStatus::class),
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->color('primary')
                    ->size('sm')
                    ->extraAttributes(['class' => 'm-4']), // زر التعديل أسفل البطاقة
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