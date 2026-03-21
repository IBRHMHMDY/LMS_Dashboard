<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth'; // أيقونة الترس للإعدادات
    protected static ?int $navigationSort = 10; // وضعها في أسفل القائمة الجانبية

    public static function getNavigationGroup(): ?string
    {
        return __('System Management'); // قسم إدارة النظام
    }

    public static function getModelLabel(): string
    {
        return __('Setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Settings');
    }

    // السماح بإضافة إعدادات جديدة؟ (غالباً الإعدادات تكون صف واحد فقط، لذا نمنع الإضافة إذا كان هناك سجل بالفعل)
    public static function canCreate(): bool
    {
        return Setting::count() === 0;
    }

    // نمنع الحذف حتى لا ينهار الموقع إذا تم مسح الإعدادات الأساسية
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        // التبويب الأول: الإعدادات العامة (الاسم، الشعار، الوصف)
                        Forms\Components\Tabs\Tab::make(__('General Settings'))
                            ->icon('heroicon-o-computer-desktop')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    // 💡 ملاحظة: قم بتغيير أسماء الحقول (site_name, logo...) لتطابق ما لديك في قاعدة البيانات
                                    Forms\Components\TextInput::make('site_name')
                                        ->label(__('Site Name'))
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('contact_email')
                                        ->label(__('Contact Email'))
                                        ->email()
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\FileUpload::make('site_logo')
                                        ->label(__('Site Logo'))
                                        ->image()
                                        ->directory('settings')
                                        ->columnSpan(1),

                                    Forms\Components\FileUpload::make('site_favicon')
                                        ->label(__('Favicon'))
                                        ->image()
                                        ->directory('settings')
                                        ->columnSpan(1),

                                    Forms\Components\Textarea::make('about_us')
                                        ->label(__('About Us (Footer Text)'))
                                        ->rows(4)
                                        ->columnSpanFull(),
                                ]),
                            ]),

                        // التبويب الثاني: وسائل التواصل الاجتماعي
                        Forms\Components\Tabs\Tab::make(__('Social Media'))
                            ->icon('heroicon-o-share')
                            ->schema([
                                Forms\Components\TextInput::make('facebook_url')
                                    ->label(__('Facebook URL'))
                                    ->url()
                                    ->prefixIcon('heroicon-m-link'),

                                Forms\Components\TextInput::make('twitter_url')
                                    ->label(__('Twitter URL'))
                                    ->url()
                                    ->prefixIcon('heroicon-m-link'),

                                Forms\Components\TextInput::make('instagram_url')
                                    ->label(__('Instagram URL'))
                                    ->url()
                                    ->prefixIcon('heroicon-m-link'),

                                Forms\Components\TextInput::make('linkedin_url')
                                    ->label(__('LinkedIn URL'))
                                    ->url()
                                    ->prefixIcon('heroicon-m-link'),
                            ])->columns(2),

                        // التبويب الثالث: إعدادات مالية (اختياري)
                        Forms\Components\Tabs\Tab::make(__('Financial Settings'))
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\TextInput::make('platform_commission')
                                    ->label(__('Platform Commission (%)'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->helperText(__('Percentage taken by the platform from each course sale.'))
                                    ->prefix('%'),
                            ]),
                    ])
                    ->columnSpanFull(), // لجعل التبويبات تملأ الشاشة
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('site_logo')
                    ->label(__('Logo'))
                    ->circular(),

                Tables\Columns\TextColumn::make('site_name')
                    ->label(__('Site Name'))
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('contact_email')
                    ->label(__('Contact Email'))
                    ->icon('heroicon-m-envelope'),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('Edit Settings')),
            ])
            ->bulkActions([
                // لا نحتاج لأوامر جماعية هنا لحماية الإعدادات
            ]);
    }

    public static function getPages(): array
    {
        return [
            // سنستخدم مسار Manage للتحكم في الإعدادات بشكل أسرع
            'index' => Pages\ManageSettings::route('/'),
        ];
    }
}