<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CourseResource;
use App\Models\Course;
use App\Services\Api\EnrollmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class EnrollmentController extends Controller
{
    use ApiResponse;

    public function __construct(private EnrollmentService $enrollmentService)
    {
    }

    // شراء / التسجيل في كورس
    public function enroll(Request $request, Course $course)
    {
        try {
            $this->enrollmentService->enroll($request->user(), $course);
            
            return $this->successResponse(null, 'Successfully enrolled in the course.', 201);
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    // جلب كورسات الطالب الحالية (My Learning)
    public function myCourses(Request $request)
    {
        $courses = $this->enrollmentService->getMyCourses($request->user());
        
        return $this->successResponse(
            CourseResource::collection($courses), 
            'My learning courses retrieved successfully.'
        );
    }

    // التحقق من حالة الاشتراك في كورس معين
    public function checkStatus(Request $request, Course $course)
    {
        $isEnrolled = $this->enrollmentService->isEnrolled($request->user(), $course);
        
        return $this->successResponse([
            'is_enrolled' => $isEnrolled
        ], 'Enrollment status retrieved.');
    }
}