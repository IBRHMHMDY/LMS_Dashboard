<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('subtitle')->nullable();
            $table->longText('description');
            
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('discount_price', 10, 2)->nullable();
            
            $table->string('level')->default('beginner'); // App\Enums\CourseLevel
            $table->string('status')->default('draft');   // App\Enums\CourseStatus
            
            $table->string('thumbnail')->nullable();
            $table->string('promo_video_url')->nullable();
            
            // JSON fields for dynamic lists
            $table->json('requirements')->nullable();
            $table->json('what_you_will_learn')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};