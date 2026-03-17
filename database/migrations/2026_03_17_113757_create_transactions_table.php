<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // user_id هو الطالب الذي قام بالشراء
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // instructor_id هو صاحب الكورس لتسهيل الاستعلام عن أرباحه
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            
            $table->string('transaction_number')->unique(); // رقم مرجعي داخلي
            
            $table->decimal('amount', 10, 2); // المبلغ الإجمالي المدفوع
            $table->decimal('platform_commission', 10, 2)->default(0.00); // عمولة المنصة
            $table->decimal('instructor_commission', 10, 2)->default(0.00); // ربح المدرب الصافي
            
            $table->string('status')->default('pending'); // App\Enums\TransactionStatus
            
            $table->string('payment_gateway')->nullable(); // stripe, paymob, etc.
            $table->string('payment_gateway_reference')->nullable(); // ID من بوابة الدفع
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};