<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\Course;
use App\Models\Enrollment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class InstructorStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1; // الترتيب في الصفحة

    protected function getStats(): array
    {
        $instructorId = Auth::id();

        $totalCourses = Course::where('instructor_id', $instructorId)->count();
        
        // استعلام لجلب عدد الطلاب المشتركين في كورسات هذا المدرب
        $totalStudents = Enrollment::whereHas('course', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        })->count();

        // إجمالي الأرباح (من جدول Transactions بناءً على العمولات)
        $totalEarnings = \App\Models\Transaction::where('instructor_id', $instructorId)
            ->where('status', 'completed')
            ->sum('instructor_earning');

        return [
            Stat::make('My Courses', $totalCourses)
                ->description('Total published and draft courses')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Total Students', $totalStudents)
                ->description('Active enrollments')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Total Earnings', '$' . number_format($totalEarnings, 2))
                ->description('Net profit from sales')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}