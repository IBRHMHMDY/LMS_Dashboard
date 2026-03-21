<?php

namespace App\Filament\Resources;

use App\Enums\TransactionStatus;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card'; // أيقونة الدفع
    protected static ?int $navigationSort = 2; // لتظهر فوق Payout Requests

    public static function getNavigationGroup(): ?string
    {
        return __('Financial Management'); // وضعها في القسم المالي
    }

    public static function getModelLabel(): string
    {
        return __('Student Payment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Student Payments');
    }

    // 1. منع الإضافة والتعديل (للمراقبة والاعتماد فقط)
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    // لن نستخدم الـ Form لأننا منعنا الإضافة والتعديل
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // بيانات الطالب (الاسم وتحته الإيميل)
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Student'))
                    ->description(fn (Transaction $record): string => $record->user->email ?? '')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-user'),

                // بيانات الكورس
                Tables\Columns\TextColumn::make('course.title')
                    ->label(__('Course'))
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->limit(30),

                // رقم العملية
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label(__('Transaction Ref'))
                    ->searchable()
                    ->copyable()
                    ->color('gray'),

                // المبلغ
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                // بوابة الدفع
                Tables\Columns\TextColumn::make('payment_gateway')
                    ->label(__('Gateway'))
                    ->searchable()
                    ->badge()
                    ->color('info'),

                // الحالة (ألوان تلقائية من الـ Enum)
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),

                // التاريخ
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                // فلاتر قوية للمدير المالي
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(TransactionStatus::class),

                Tables\Filters\SelectFilter::make('payment_gateway')
                    ->label(__('Gateway'))
                    ->options([
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                        'paymob' => 'Paymob',
                        'vodafone_cash' => 'Vodafone Cash',
                        'instapay' => 'InstaPay',
                        'bank_transfer' => 'Bank Transfer',
                    ]), // يمكنك تعديل هذه الخيارات حسب البوابات المتاحة لديك
            ])
            ->actions([
                // 1. زر الاعتماد المركزي
                Tables\Actions\Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === TransactionStatus::PENDING)
                    ->action(function ($record) {
                        $record->update(['status' => TransactionStatus::COMPLETED]);
                        
                        // تفعيل الاشتراك
                        \App\Models\Enrollment::updateOrCreate(
                            ['user_id' => $record->user_id, 'course_id' => $record->course_id],
                            ['is_active' => true, 'enrolled_at' => now()]
                        );
                    }),

                // 2. زر الرفض المركزي
                Tables\Actions\Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === TransactionStatus::PENDING)
                    ->action(function ($record) {
                        $record->update(['status' => TransactionStatus::FAILED]);
                        
                        // إيقاف الاشتراك
                        \App\Models\Enrollment::where('user_id', $record->user_id)
                            ->where('course_id', $record->course_id)
                            ->update(['is_active' => false]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
}