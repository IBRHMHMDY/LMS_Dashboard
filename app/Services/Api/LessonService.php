<?php

namespace App\Services\Api;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Exception;

class LessonService
{
    /**
     * جلب تفاصيل الدرس بعد التحقق من الصلاحيات
     */
    public function getLessonDetails(User $user, Lesson $lesson): Lesson
    {
        $lesson->load('section.course');
        $courseId = $lesson->section->course_id;

        // إذا لم يكن الدرس مجانياً، يجب التحقق من اشتراك الطالب
        if (!$lesson->is_free_preview) {
            $isEnrolled = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->where('status', 'active')
                ->exists();

            if (!$isEnrolled) {
                throw new Exception('Access Denied: You must enroll in the course to view this lesson.', 403);
            }
        }

        return $lesson;
    }

    /**
     * تبديل حالة إكمال الدرس (Mark as complete / incomplete)
     */
    public function toggleProgress(User $user, Lesson $lesson): bool
    {
        $lesson->load('section.course');
        $courseId = $lesson->section->course_id;

        // يجب أن يكون الطالب مشتركاً في الكورس ليتمكن من تتبع تقدمه
        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->exists();

        if (!$isEnrolled) {
            throw new Exception('Action Denied: You must enroll in the course to track progress.', 403);
        }

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        // إذا كان مكتملاً، اجعله غير مكتمل
        if ($progress && $progress->completed_at !== null) {
            $progress->update(['completed_at' => null]);
            return false;
        }

        // إذا لم يكن مكتملاً، اجعله مكتملاً
        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['completed_at' => now()]
        );

        return true;
    }
}