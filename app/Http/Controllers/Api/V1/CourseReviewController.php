<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Course\StoreCourseReviewRequest;
use App\Http\Resources\Api\CourseReviewResource;
use App\Models\Course;
use App\Services\Api\CourseReviewService;
use App\Traits\ApiResponse;
use Exception;

class CourseReviewController extends Controller
{
    use ApiResponse;

    protected CourseReviewService $reviewService;

    public function __construct(CourseReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index(Course $course)
    {
        $reviews = $this->reviewService->getCourseReviews($course);

        // Here we use resource collection directly for pagination metadata
        return CourseReviewResource::collection($reviews)->additional([
            'success' => true,
            'message' => 'Reviews retrieved successfully.'
        ]);
    }

    public function store(StoreCourseReviewRequest $request, Course $course)
    {
        try {
            $review = $this->reviewService->submitReview($request->user(), $course, $request->validated());

            return $this->successResponse(
                new CourseReviewResource($review),
                'Review submitted successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403); // Forbidden because rule violated
        }
    }
}