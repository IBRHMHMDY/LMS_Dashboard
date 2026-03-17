<?php

namespace App\Filament\Widgets;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // حساب إجمالي أرباح المنصة (عمولة المنصة من المعاملات المكتملة)
        $platformEarnings = Transaction::where('status', 'completed')->sum('platform_commission');

        // إحصائيات المستخدمين
        $totalStudents = User::role('student')->count();
        $totalInstructors = User::role('instructor')->count();

        // الكورسات التي تنتظر المراجعة
        $pendingCourses = Course::where('status', CourseStatus::PENDING->value)->count();

        return [
            Stat::make('Platform Revenue', '$' . number_format($platformEarnings, 2))
                ->description('Total net profit for the platform')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Users', $totalStudents + $totalInstructors)
                ->description($totalStudents . ' Students | ' . $totalInstructors . ' Instructors')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Pending Courses', $pendingCourses)
                ->description('Courses waiting for approval')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($pendingCourses > 0 ? 'warning' : 'gray'),
        ];
    }
}