<?php

namespace App\Filament\Resources\PayoutRequestResource\Pages;

use App\Enums\PayoutStatus;
use App\Filament\Resources\PayoutRequestResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewPayoutRequest extends ViewRecord
{
    protected static string $resource = PayoutRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pay')
                ->label('Mark as Paid')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === PayoutStatus::PENDING)
                ->form([
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Transfer Reference / Notes')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => PayoutStatus::PAID,
                        'admin_notes' => $data['admin_notes'],
                        'processed_at' => now(),
                    ]);
                    $this->refreshFormData(['status', 'admin_notes']);
                }),

            Actions\Action::make('reject')
                ->label('Reject Request')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === PayoutStatus::PENDING)
                ->form([
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Reason for Rejection')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => PayoutStatus::REJECTED,
                        'admin_notes' => $data['admin_notes'],
                        'processed_at' => now(),
                    ]);
                    $this->refreshFormData(['status', 'admin_notes']);
                }),
        ];
    }
}