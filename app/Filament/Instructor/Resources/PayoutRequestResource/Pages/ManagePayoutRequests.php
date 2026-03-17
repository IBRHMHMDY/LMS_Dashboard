<?php

namespace App\Filament\Instructor\Resources\PayoutRequestResource\Pages;

use App\Filament\Instructor\Resources\PayoutRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManagePayoutRequests extends ManageRecords
{
    protected static string $resource = PayoutRequestResource::class;

    // تخصيص الـ Breadcrumbs
    public function getBreadcrumbs(): array
    {
        return [
            url('/instructor') => 'Dashboard',
            'Payout Requests',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Request Payout')
                ->icon('heroicon-o-currency-dollar')
                ->modalWidth('md')
                ->modalHeading('Submit Payout Request')
                ->mutateFormDataUsing(function (array $data): array {
                    // ربط الطلب بالمدرب الحالي برمجياً
                    $data['user_id'] = Auth::id();
                    return $data;
                }),
        ];
    }
}