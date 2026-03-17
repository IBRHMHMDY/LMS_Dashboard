<?php

namespace App\Filament\Instructor\Widgets;

use Filament\Widgets\ChartWidget;

class InstructorSalesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Earnings';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // في بيئة العمل الحقيقية سيتم جلب هذه البيانات عبر استعلام GroupBy من جدول Transactions
        // هذا مجرد Mocking مؤقت ليظهر الرسم البياني للمدرب
        return [
            'datasets' => [
                [
                    'label' => 'Earnings ($)',
                    'data' => [0, 150, 200, 350, 600, 450, 800, 900, 1200, 1000, 1500, 2000],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => 'start',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line'; // نوع الرسم البياني
    }
}