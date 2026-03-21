<?php

namespace App\Enums;

enum StatusColor: string
{
    case SUCCESS = 'success'; // للأشياء الإيجابية (مفعل، مكتمل، مدفوع، مجاني)
    case DANGER  = 'danger';  // للأشياء السلبية (محذوف، مرفوض، فشل، غير مفعل)
    case WARNING = 'warning'; // للأشياء المعلقة (قيد الانتظار، مراجعة)
    case INFO    = 'info';    // للمعلومات (قيد المعالجة، عدد الطلاب، نوع الدرس)
    case PRIMARY = 'primary'; // للون الأساسي للمنصة (الأزرار الأساسية)
    case GRAY    = 'gray';    // للأشياء المحايدة (مسودة، غير محدد، ترتيب)

    /**
     * دالة مساعدة للحصول على اللون مباشرة
     */
    public function value(): string
    {
        return $this->value;
    }
}