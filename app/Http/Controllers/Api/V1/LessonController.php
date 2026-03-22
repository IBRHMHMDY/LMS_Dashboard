<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\Lesson\SyncProgressRequest;
use App\Models\LessonProgress;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LessonDetailResource;
use App\Models\Lesson;
use App\Services\Api\LessonService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class LessonController extends Controller
{
    use ApiResponse;

    public function __construct(private LessonService $lessonService)
    {
    }

    // عرض تفاصيل الدرس (محمي)
    public function show(Request $request, Lesson $lesson)
    {
        try {
            $validLesson = $this->lessonService->getLessonDetails($request->user(), $lesson);
            
            return $this->successResponse(
                new LessonDetailResource($validLesson),
                'Lesson details retrieved successfully.'
            );
        } catch (Exception $e) {
            $code = $e->getCode() == 403 ? 403 : 400;
            return $this->errorResponse($e->getMessage(), null, $code);
        }
    }

    // وضع علامة مكتمل / غير مكتمل
    public function toggleComplete(Request $request, Lesson $lesson)
    {
        try {
            $isCompleted = $this->lessonService->toggleProgress($request->user(), $lesson);
            
            $message = $isCompleted ? 'Lesson marked as completed.' : 'Lesson marked as incomplete.';
            
            return $this->successResponse(['is_completed' => $isCompleted], $message);
        } catch (Exception $e) {
            $code = $e->getCode() == 403 ? 403 : 400;
            return $this->errorResponse($e->getMessage(), null, $code);
        }
    }

    /**
     * Sync video watch time from mobile app
     */
    public function syncProgress(SyncProgressRequest $request, Lesson $lesson)
    {
        // 1. التحقق من أن المستخدم مسجل في الكورس (يمكن نقلها للـ Service مستقبلاً)
        $isEnrolled = $request->user()->enrollments()
            ->where('course_id', $lesson->section->course_id)
            ->exists();

        if (!$isEnrolled && !$lesson->is_free_preview) {
            return $this->errorResponse('You must be enrolled to track progress.', null, 403);
        }

        // 2. تحديث أو إنشاء سجل التقدم
        $progress = LessonProgress::firstOrNew([
            'user_id' => $request->user()->id,
            'lesson_id' => $lesson->id
        ]);

        $progress->watch_time_in_seconds = $request->validated('watch_time_in_seconds');
        
        // 3. تحديد الدرس كمكتمل إذا أرسل الموبايل is_completed ولم يكن مكتملاً من قبل
        if ($request->validated('is_completed') && !$progress->completed_at) {
            $progress->completed_at = now();
        }

        $progress->save();

        return $this->successResponse(
            ['watch_time_in_seconds' => $progress->watch_time_in_seconds, 'is_completed' => $progress->completed_at !== null], 
            'Progress synced successfully.'
        );
    }
}