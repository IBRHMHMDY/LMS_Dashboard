<?php

namespace App\Filament\Resources;

use App\Enums\PayoutStatus;
use App\Filament\Resources\PayoutRequestResource\Pages;
use App\Models\PayoutRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayoutRequestResource extends Resource
{
    protected static ?string $model = PayoutRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Financials';
    protected static ?int $navigationSort = 1;

    // منع الإدارة من إنشاء طلبات سحب
    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Request Details')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Instructor Name')
                            ->icon('heroicon-o-user')
                            ->weight('bold'),
                        
                        Infolists\Components\TextEntry::make('amount')
                            ->money('USD')
                            ->color('success')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (PayoutStatus $state): string => match ($state) {
                                PayoutStatus::PENDING => 'warning',
                                PayoutStatus::APPROVED => 'primary',
                                PayoutStatus::PAID => 'success',
                                PayoutStatus::REJECTED => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Requested At')
                            ->dateTime(),
                    ]),

                Infolists\Components\Section::make('Notes & Feedback')
                    ->columns(1)
                    ->schema([
                        Infolists\Components\TextEntry::make('instructor_notes')
                            ->label('Payment Destination (Instructor Notes)')
                            ->prose(),

                        Infolists\Components\TextEntry::make('admin_notes')
                            ->label('Admin Notes / Transfer Reference')
                            ->color('gray')
                            ->visible(fn ($record) => $record->admin_notes !== null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Instructor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (PayoutStatus $state): string => match ($state) {
                        PayoutStatus::PENDING => 'warning',
                        PayoutStatus::APPROVED => 'primary',
                        PayoutStatus::PAID => 'success',
                        PayoutStatus::REJECTED => 'danger',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(PayoutStatus::class),
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Instructor'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // إجراء الدفع السريع (يطلب إدخال رقم الحوالة)
                Tables\Actions\Action::make('pay')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PayoutRequest $record) => $record->status === PayoutStatus::PENDING)
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Transfer Reference / Notes')
                            ->required()
                            ->placeholder('e.g., Transferred via PayPal, TxID: 123456789'),
                    ])
                    ->action(function (PayoutRequest $record, array $data) {
                        $record->update([
                            'status' => PayoutStatus::PAID,
                            'admin_notes' => $data['admin_notes'],
                            'processed_at' => now(),
                        ]);
                    }),

                // إجراء الرفض السريع (يطلب إدخال سبب الرفض)
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PayoutRequest $record) => $record->status === PayoutStatus::PENDING)
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Reason for Rejection')
                            ->required()
                            ->placeholder('e.g., Invalid bank account details.'),
                    ])
                    ->action(function (PayoutRequest $record, array $data) {
                        $record->update([
                            'status' => PayoutStatus::REJECTED,
                            'admin_notes' => $data['admin_notes'],
                            'processed_at' => now(),
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayoutRequests::route('/'),
            'view' => Pages\ViewPayoutRequest::route('/{record}'),
        ];
    }
}