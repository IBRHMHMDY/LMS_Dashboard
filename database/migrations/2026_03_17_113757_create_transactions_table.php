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
            
            // العلاقات
            // user_id هو الطالب الذي قام بالشراء
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // instructor_id هو صاحب الكورس لتسهيل الاستعلام عن أرباحه في لوحة التحكم
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            
            // رقم مرجعي داخلي للمنصة
            $table->string('transaction_number')->unique(); 
            
            // التفاصيل المالية
            $table->decimal('amount', 10, 2); // المبلغ الإجمالي المدفوع
            $table->decimal('platform_commission', 10, 2)->default(0.00); // عمولة المنصة
            $table->decimal('instructor_earning', 10, 2)->default(0.00); // ربح المدرب الصافي
            
            // حالة العملية (App\Enums\TransactionStatus)
            $table->string('status')->default('pending'); 
            
            // بوابات الدفع (Payment Gateway & IAP)
            $table->string('payment_method')->nullable(); // طريقة الدفع (مثل: in_app_purchase, credit_card)
            $table->string('payment_gateway')->nullable(); // بوابة الدفع (مثل: apple_iap, google_iap, stripe)
            $table->string('gateway_transaction_id')->unique()->nullable(); // رقم العملية العائد من المتجر أو بوابة الدفع
            $table->text('receipt_data')->nullable(); // الإيصال (Token/Receipt) الخاص بـ Apple أو Google للتحقق Server-to-Server
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};