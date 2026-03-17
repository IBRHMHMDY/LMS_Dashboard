<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // المدرب
            
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // App\Enums\PayoutStatus
            
            $table->text('instructor_notes')->nullable(); // تفاصيل حساب البنك للمدرب
            $table->text('admin_notes')->nullable(); // سبب الرفض أو مرجع التحويل
            
            $table->timestamp('processed_at')->nullable(); // تاريخ الموافقة والتحويل
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};