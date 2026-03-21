<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Filament\Resources\StudentResource\RelationManagers\TransactionsRelationManager;


class StudentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap'; // أيقونة قبعة التخرج للطلاب
    
    protected static ?string $slug = 'students';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('User Management');
    }

    public static function getModelLabel(): string
    {
        return __('Student');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Students');
    }

    // منع التعديل المباشر والاكتفاء بالعرض والحذف
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    // 💡 فلترة الطلاب فقط مع حساب عدد الكورسات المشتركين بها
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role('student') // جلب الطلاب فقط
            ->withCount('enrollments'); // حساب عدد الاشتراكات (الكورسات)
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Student Information'))
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\FileUpload::make('avatar')
                                    ->label(__('Avatar'))
                                    ->image()
                                    ->avatar()
                                    ->directory('avatars')
                                    ->columnSpan(1)
                                    ->alignCenter(),

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
                                            ->revealable()
                                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->required(fn (string $context): bool => $context === 'create')
                                            ->maxLength(255),
                                    ])
                                    ->columnSpan(2)
                                    ->columns(1),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(__('Student Profile'))
                    ->schema([
                        Infolists\Components\Grid::make(3)->schema([
                            // صورة الطالب
                            Infolists\Components\ImageEntry::make('avatar')
                                ->hiddenLabel()
                                ->circular()
                                ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=random')
                                ->columnSpan(1),

                            // بياناته الشخصية
                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('name')
                                    ->label(__('Name'))
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('email')
                                    ->label(__('Email'))
                                    ->icon('heroicon-m-envelope')
                                    ->copyable()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('phone_number')
                                    ->label(__('Phone Number'))
                                    ->icon('heroicon-m-phone'),
                                    
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label(__('Joined At'))
                                    ->date('d F Y')
                                    ->icon('heroicon-m-calendar')
                                    ->color('gray'),
                            ])->columnSpan(2),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // العمود المدمج الاحترافي (نفس تصميم المدربين لتوحيد الـ UI)
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Student'))
                    ->html()
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->formatStateUsing(function (string $state, User $record): string {
                        $avatarUrl = $record->avatar 
                            ? \Illuminate\Support\Facades\Storage::url($record->avatar) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($state) . '&background=random';
                        
                        return "
                            <div class='flex items-center gap-3'>
                                <img src='{$avatarUrl}' alt='Avatar' 
                                     class='rounded-full border border-gray-200 dark:border-gray-700 shadow-sm' 
                                     style='width: 44px; height: 44px; min-width: 44px; min-height: 44px; object-fit: cover;'>
                                <div class='flex flex-col'>
                                    <span class='text-sm font-bold text-gray-900 dark:text-white leading-tight'>{$state}</span>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 mt-1'>{$record->email}</span>
                                </div>
                            </div>
                        ";
                    }),

                // عدد الكورسات التي اشتراها الطالب
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label(__('Enrolled Courses'))
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-book-open')
                    ->sortable(),

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
                Tables\Actions\ViewAction::make()->label(__('View')),
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
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'view' => Pages\ViewStudent::route('/{record}'), // أضف هذا السطر
        ];
    }
}