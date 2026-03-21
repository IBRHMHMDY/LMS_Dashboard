<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    public function getTitle(): string
    {
        return __('Student Payments');
    }

    protected function getHeaderActions(): array
    {
        // إرجاع مصفوفة فارغة يمنع ظهور زر "إضافة معاملة جديدة"
        return [];
    }
}