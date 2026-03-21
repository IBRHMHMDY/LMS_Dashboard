<?php

namespace App\Filament\Resources;

use App\Enums\PayoutStatus;
use App\Filament\Resources\PayoutRequestResource\Pages;
use App\Models\PayoutRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayoutRequestResource extends Resource
{
    protected static ?string $model = PayoutRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    // تجهيز العناوين للترجمة
    public static function getNavigationGroup(): ?string
    {
        return __('Financial Management');
    }

    public static function getModelLabel(): string
    {
        return __('Payout Request');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payout Requests');
    }

    protected static ?int $navigationSort = 3;

    // 1. التعديل الأول: إظهار الحقول المطلوب إدخالها فقط في نافذة التعديل (Modal)
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        PayoutStatus::APPROVED->value => PayoutStatus::APPROVED->getLabel(),
                        PayoutStatus::PAID->value     => PayoutStatus::PAID->getLabel(),
                        PayoutStatus::REJECTED->value => PayoutStatus::REJECTED->getLabel(),
                    ])
                    ->required(),

                Forms\Components\Textarea::make('admin_notes')
                    ->label(__('Admin Notes / Transfer Reference'))
                    ->helperText(__('Add reference number or reason for rejection.'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 2. التعديل الثاني: استخدام العلاقة الصحيحة (user) لجلب اسم المدرب
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Instructor'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),

                // 3. التعديل الثالث: إظهار بيانات البنك الصحيحة (من حقل instructor_notes)
                Tables\Columns\TextColumn::make('instructor_notes')
                    ->label(__('Bank / Payment Details'))
                    ->wrap() // التفاف النص لكي يظهر كاملاً إذا كان طويلاً
                    ->searchable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Requested At'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([PayoutStatus::class]),
                
                // تحديث الفلتر ليستخدم العلاقة user بدلاً من instructor
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('Instructor'))
                    ->relationship('user', 'name', fn (Builder $query) => $query->role('instructor'))
                    ->searchable(),
            ])
            ->actions([
                // زر التعديل يفتح Modal بحجم مناسب لحقلين فقط
                Tables\Actions\EditAction::make()
                    ->label(__('Update Status'))
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading(__('Update Payout Status'))
                    ->modalWidth('sm'), // تصغير حجم الـ Modal ليكون أنيقاً
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('Delete Selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayoutRequests::route('/'),
        ];
    }
}