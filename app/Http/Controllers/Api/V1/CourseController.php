<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CourseDetailResource;
use App\Http\Resources\Api\CourseResource;
use App\Services\Api\CourseService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use ApiResponse;

    public function __construct(private CourseService $courseService)
    {
    }

    public function index(Request $request)
    {
        $courses = $this->courseService->clonePublishedCourses($request->all(), 10);

        // دمج بيانات الـ Pagination مع رد الـ API الموحد
        $resourceCollection = CourseResource::collection($courses)->response()->getData(true);

        return $this->successResponse($resourceCollection, 'Courses retrieved successfully');
    }

    public function show($slug)
    {
        $course = $this->courseService->getCourseDetails($slug);

        return $this->successResponse(
            new CourseDetailResource($course), 
            'Course details retrieved successfully'
        );
    }
}