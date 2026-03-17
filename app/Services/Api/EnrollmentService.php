<?php

namespace App\Services\Api;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class EnrollmentService
{
    /**
     * تسجيل الطالب في الكورس مع معالجة المدفوعات (Mock)
     */
    public function enroll(User $user, Course $course): Enrollment
    {
        // 1. التحقق من عدم التسجيل المسبق
        if ($this->isEnrolled($user, $course)) {
            throw new Exception('You are already enrolled in this course.');
        }

        // 2. استخدام DB Transaction لضمان سلامة قاعدة البيانات (إما أن تكتمل العملية بالكامل أو تُلغى)
        return DB::transaction(function () use ($user, $course) {
            
            $price = $course->discount_price ?? $course->price;
            $transactionId = null;

            // إذا كان الكورس مدفوعاً، نقوم بإنشاء Transaction مالي
            if ($price > 0) {
                // جلب نسبة عمولة المنصة من الإعدادات، أو 20% كقيمة افتراضية
                $platformCommissionPercentage = Setting::where('key', 'platform_commission_percentage')->value('value') ?? 20;
                
                $platformCommission = ($price * $platformCommissionPercentage) / 100;
                $instructorEarning = $price - $platformCommission;

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'amount' => $price,
                    'platform_commission' => $platformCommission,
                    'instructor_earning' => $instructorEarning,
                    'payment_method' => 'mock_payment', // محاكاة للدفع لتطبيق Flutter
                    'status' => 'completed',
                ]);
                
                $transactionId = $transaction->id;
            }

            // 3. إنشاء سجل الانضمام (Enrollment) ليتمكن الطالب من مشاهدة الدروس
            return Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'transaction_id' => $transactionId,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        });
    }

    /**
     * جلب الكورسات التي سجل فيها الطالب (My Learning)
     */
    public function getMyCourses(User $user)
    {
        // نستخدم Pluck لاستخراج موديل الـ Course مباشرة من علاقة הـ Enrollments
        return $user->enrollments()
            ->with(['course.instructor', 'course.category']) // Eager Loading لتسريع الـ API
            ->where('status', 'active')
            ->get()
            ->pluck('course');
    }

    /**
     * التحقق مما إذا كان الطالب مسجلاً في الكورس (لإخفاء زر الشراء في التطبيق)
     */
    public function isEnrolled(User $user, Course $course): bool
    {
        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();
    }
}