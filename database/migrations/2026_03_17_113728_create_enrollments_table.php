<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            
            $table->timestamp('enrolled_at')->useCurrent();
            $table->boolean('is_active')->default(true); // يمكن إيقاف اشتراك طالب عند استرجاع الأموال
            
            $table->timestamps();
            
            // الطالب يشترك في الكورس مرة واحدة فقط
            $table->unique(['user_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};