<?php

namespace App\Filament\Instructor\Resources;

use App\Enums\PayoutStatus;
use App\Filament\Instructor\Resources\PayoutRequestResource\Pages;
use App\Models\PayoutRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PayoutRequestResource extends Resource
{
    protected static ?string $model = PayoutRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Payouts';
    protected static ?string $modelLabel = 'Payout Request';
    protected static ?int $navigationSort = 4; // يظهر في نهاية القائمة

    // قصر عرض الطلبات على المدرب الحالي فقط
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(50) // حد أدنى للسحب (تستطيع تغييره)
                    ->helperText('Minimum payout amount is $50.'),

                Forms\Components\Textarea::make('instructor_notes')
                    ->label('Payment Details / Notes')
                    ->placeholder('e.g., PayPal email, Bank Account details...')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),

                // عرض حالة الطلب بتنسيق لوني مخصص
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (PayoutStatus $state): string => match ($state) {
                        PayoutStatus::PENDING => 'warning',
                        PayoutStatus::APPROVED => 'primary',
                        PayoutStatus::PAID => 'success',
                        PayoutStatus::REJECTED => 'danger',
                    }),

                Tables\Columns\TextColumn::make('admin_notes')
                    ->label('Admin Feedback')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(PayoutStatus::class),
            ])
            ->actions([
                // إخفاء زر التعديل إذا لم يكن الطلب قيد الانتظار
                Tables\Actions\EditAction::make()
                    ->modalWidth('md')
                    ->visible(fn (PayoutRequest $record): bool => $record->status === PayoutStatus::PENDING),
                
                // إخفاء زر الحذف إذا لم يكن الطلب قيد الانتظار
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (PayoutRequest $record): bool => $record->status === PayoutStatus::PENDING),
            ])
            ->bulkActions([]) // إيقاف الحذف الجماعي للحماية المالية
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayoutRequests::route('/'),
        ];
    }
}