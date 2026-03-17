<?php

namespace App\Filament\Resources\PayoutRequestResource\Pages;

use App\Enums\PayoutStatus;
use App\Filament\Resources\PayoutRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayoutRequests extends ListRecords
{
    protected static string $resource = PayoutRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Requests'),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PayoutStatus::PENDING))
                ->badge(PayoutRequestResource::getEloquentQuery()->where('status', PayoutStatus::PENDING)->count())
                ->badgeColor('warning'),
            'paid' => Tab::make('Paid')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PayoutStatus::PAID)),
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PayoutStatus::REJECTED)),
        ];
    }
}