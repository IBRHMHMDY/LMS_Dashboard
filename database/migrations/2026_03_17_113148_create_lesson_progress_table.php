<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('watch_time_in_seconds')->default(0);
            $table->timestamp('completed_at')->nullable(); // إذا كان Null فالدرس غير مكتمل
            
            $table->timestamps();
            
            // منع تكرار نفس الدرس لنفس المستخدم
            $table->unique(['user_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};