<?php

namespace App\Http\Controllers\Api\V1;

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
}