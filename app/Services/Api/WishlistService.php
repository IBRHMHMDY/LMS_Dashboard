<?php

namespace App\Services\Api;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class WishlistService
{
    /**
     * Toggles the course in the user's wishlist.
     * Returns true if added, false if removed.
     */
    public function toggleWishlist(User $user, Course $course): bool
    {
        $wishlist = $user->wishlists()->where('course_id', $course->id)->first();

        if ($wishlist) {
            $wishlist->delete();
            return false; // Removed
        }

        $user->wishlists()->create([
            'course_id' => $course->id
        ]);

        return true; // Added
    }

    /**
     * Retrieves the user's wishlisted courses.
     */
    public function getUserWishlist(User $user): Collection
    {
        return Course::whereHas('wishlists', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['instructor', 'category'])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->get();
    }
}