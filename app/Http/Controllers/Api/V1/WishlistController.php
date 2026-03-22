<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CourseResource;
use App\Models\Course;
use App\Services\Api\WishlistService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use ApiResponse;

    protected WishlistService $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    public function index(Request $request)
    {
        $courses = $this->wishlistService->getUserWishlist($request->user());

        return $this->successResponse(
            CourseResource::collection($courses),
            'Wishlist retrieved successfully.'
        );
    }

    public function toggle(Request $request, Course $course)
    {
        $isAdded = $this->wishlistService->toggleWishlist($request->user(), $course);

        $message = $isAdded ? 'Course added to wishlist successfully.' : 'Course removed from wishlist successfully.';

        return $this->successResponse(
            ['is_in_wishlist' => $isAdded],
            $message
        );
    }
}