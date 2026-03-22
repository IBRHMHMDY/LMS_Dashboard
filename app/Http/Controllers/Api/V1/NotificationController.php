<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        // جلب الإشعارات مع دعم الـ Pagination
        $notifications = $request->user()->notifications()->paginate(15);

        return NotificationResource::collection($notifications)->additional([
            'success' => true,
            'message' => 'Notifications retrieved successfully.'
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        
        $notification->markAsRead();

        return $this->successResponse(null, 'Notification marked as read successfully.');
    }
}