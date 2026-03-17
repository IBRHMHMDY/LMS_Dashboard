<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable(); // يستخدم للدروس النصية
            
            $table->string('lesson_type')->default('video_url'); // App\Enums\LessonType
            $table->string('video_url')->nullable();
            $table->integer('duration_in_minutes')->nullable(); // لحساب إجمالي مدة الكورس
            
            $table->integer('order')->default(0);
            $table->boolean('is_free_preview')->default(false); // هل الدرس مجاني كعينة؟
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};