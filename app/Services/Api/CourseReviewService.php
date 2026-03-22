<?php

namespace App\Services\Api;

use App\Models\Course;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourseReviewService
{
    /**
     * @throws Exception
     */
    public function submitReview(User $user, Course $course, array $data)
    {
        // Business Rule: The user must be enrolled in the course to review it.
        $isEnrolled = $user->enrollments()->where('course_id', $course->id)->exists();

        if (!$isEnrolled) {
            throw new Exception("You must be enrolled in this course to submit a review.");
        }

        // Upsert the review (update if exists, create if not)
        return $user->courseReviews()->updateOrCreate(
            ['course_id' => $course->id],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );
    }

    public function getCourseReviews(Course $course): LengthAwarePaginator
    {
        return $course->reviews()
            ->with('user')
            ->latest()
            ->paginate(15);
    }
}