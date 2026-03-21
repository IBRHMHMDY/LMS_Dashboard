<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Enums\TransactionStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionsRelationManager extends RelationManager
{
    // اسم العلاقة في موديل User
    protected static string $relationship = 'transactions';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Payment History');
    }

    // لا نريد إضافة فورم هنا، لأن المدفوعات تُسجل أوتوماتيكياً ولا يضيفها المدير يدوياً
    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_id')
            ->columns([
                // اسم الكورس الذي اشتراه
                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('Course'))
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-m-book-open'),

                // رقم العملية (Reference)
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label(__('Transaction ID'))
                    ->searchable()
                    ->copyable() // السماح بنسخ الرقم بضغطة زر
                    ->color('gray'),

                // المبلغ المدفوع
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                // طريقة الدفع
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Method'))
                    ->badge()
                    ->color('info'),

                // حالة الدفع (تتلون تلقائياً من الـ Enum)
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),

                // تاريخ الدفع
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // يمكنك إضافة زر عرض تفاصيل العملية إن أردت لاحقاً
            ])
            ->defaultSort('created_at', 'desc');
    }
}