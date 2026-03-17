<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        // جلب الأقسام الرئيسية فقط مع الأقسام الفرعية التابعة لها
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            CategoryResource::collection($categories), 
            'Categories retrieved successfully'
        );
    }
}