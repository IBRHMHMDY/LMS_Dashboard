<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstructorResource\Pages;
use App\Filament\Resources\InstructorResource\RelationManagers\CoursesRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Pages\ViewInstructor;

class InstructorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    
    protected static ?string $slug = 'instructors';
    
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('User Management');
    }

    public static function getModelLabel(): string
    {
        return __('Instructor');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Instructors');
    }

    // 1. منع التعديل برمجياً على مستوى المورد (Resource)
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    // 2. الفلترة والإحصائيات الذكية
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role('instructor') // فلترة المدربين فقط
            // حساب الكورسات (المنشورة فقط)
            ->withCount(['coursesAsInstructor as published_courses_count' => fn (Builder $query) => $query->where('status', 'published')])
            // حساب إجمالي الطلاب المشتركين في جميع كورسات هذا المدرب
            ->addSelect(['total_students' => \App\Models\Enrollment::selectRaw('count(*)')
                ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                ->whereColumn('courses.instructor_id', 'users.id')
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // القسم الأول: البيانات الشخصية (تصميم Side-by-Side)
                Forms\Components\Section::make(__('Personal Information'))
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                // العمود الأول: الصورة الدائرية
                                Forms\Components\FileUpload::make('avatar')
                                    ->label(__('Avatar'))
                                    ->image()
                                    ->avatar()
                                    ->directory('avatars')
                                    ->columnSpan(1)
                                    ->alignCenter(),

                                // العمودان الآخران: حقول الإدخال
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('email')
                                            ->label(__('Email'))
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('phone_number')
                                            ->label(__('Phone Number'))
                                            ->tel()
                                            ->maxLength(20),

                                        Forms\Components\TextInput::make('password')
                                            ->label(__('Password'))
                                            ->password()
                                            ->revealable() // show/hide Password
                                            ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->required(fn (string $context): bool => $context === 'create')
                                            ->maxLength(255),
                                    ])
                                    ->columnSpan(2)
                                    ->columns(1),
                            ]),
                    ]),

                // القسم الثاني: البيانات المهنية
                Forms\Components\Section::make(__('Professional Details'))
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Forms\Components\TextInput::make('headline')
                            ->label(__('Headline / Title'))
                            ->helperText(__('e.g. Senior Laravel Developer'))
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('bio')
                            ->label(__('Biography / About'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                // القسم الثالث: البيانات المالية
                Forms\Components\Section::make(__('Financial & Payment Details'))
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\Textarea::make('instructor_notes')
                            ->label(__('Bank Account Details'))
                            ->helperText(__('Enter the bank name, account name, and account number for future payouts.'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('Personal Information'))
                    ->schema([
                        Grid::make(3)->schema([
                            // الصورة
                            Group::make([
                                ImageEntry::make('avatar')
                                    ->hiddenLabel()
                                    ->circular()
                                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=random'),
                                TextEntry::make('headline')
                                    ->label(__('Headline / Title'))
                                    ->columnSpanFull()
                                    ->color('gray'),
                            ])
                            ->columnSpan(1),

                            // البيانات النصية
                            Group::make([
                                TextEntry::make('name')
                                    ->label(__('Name'))
                                    ->weight('bold')
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('email')
                                    ->label(__('Email'))
                                    ->icon('heroicon-m-envelope')
                                    ->color('primary'),

                                TextEntry::make('phone_number')
                                    ->label(__('Phone Number'))
                                    ->icon('heroicon-m-phone'),
                            ])->columnSpan(2),
                        ]),
                    ]),

                Section::make(__('Professional & Financial Details'))
                    ->columns(2)
                    ->schema([
                        

                        TextEntry::make('bio')
                            ->label(__('Biography'))
                            ->columnSpanFull(),

                        TextEntry::make('instructor_notes')
                            ->label(__('Bank Account Details'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 3. العمود المدمج (صورة دائرية + اسم + إيميل)
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Instructor'))
                    ->html()
                    ->searchable(['name', 'email']) // ذكاء البحث: يبحث بالاسم أو الإيميل معاً
                    ->sortable()
                    ->formatStateUsing(function (string $state, User $record): string {
                        
                        $avatarUrl = $record->avatar 
                            ? \Illuminate\Support\Facades\Storage::url($record->avatar) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($state) . '&background=random';
                        
                        // تصميم متناسق وأنيق يثبت حجم الصورة الدائرية
                        return "
                            <div class='flex items-center gap-3'>
                                <img src='{$avatarUrl}' alt='Avatar' 
                                     class='rounded-full border border-gray-200 dark:border-gray-700 shadow-sm' 
                                     style='width: 44px; height: 44px; min-width: 44px; min-height: 44px; object-fit: cover;'>
                                <div class='flex flex-col'>
                                    <span class='text-sm font-bold text-gray-900 dark:text-white leading-tight'>{$state}</span>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 mt-1'>{$record->email}</span>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 mt-1'>{$record->phone_number}</span>
                                </div>
                            </div>
                        ";
                    }),

                // 4. عدد الكورسات المنشورة
                Tables\Columns\TextColumn::make('published_courses_count')
                    ->label(__('Published Courses'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                // 5. عدد الطلاب المشتركين
                Tables\Columns\TextColumn::make('total_students')
                    ->label(__('Total Students'))
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-users')
                    ->sortable(),

                // 6. تاريخ الالتحاق
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Joined At'))
                    ->date('d M Y')
                    ->sortable()
                    ->color('gray'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // السماح بالحذف فقط، وتم إزالة زر التعديل
                Tables\Actions\DeleteAction::make()->label(__('Delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('Delete Selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            CoursesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstructors::route('/'),
            'create' => Pages\CreateInstructor::route('/create'),
            'view' => Pages\ViewInstructor::route('/{record}'),
        ];
    }
}