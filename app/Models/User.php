<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'headline',
        'phone_number',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean'
        ];
    }

    // تحديد صلاحية الدخول للوحات Filament
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole('admin');
        }

        if ($panel->getId() === 'instructor') {
            return $this->hasRole('instructor');
        }

        return false;
    }
    /**
     * علاقة المدرب بكورساته التى انشاها
     */
    public function coursesAsInstructor(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * علاقة الطالب بالاشتراكات (الكورسات التي التحق بها)
     */
    public function enrollments()
    {
        return $this->hasMany(\App\Models\Enrollment::class, 'user_id');
    }

    /**
     * علاقة الطالب بسجل المدفوعات (العمليات المالية)
     */
    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class, 'user_id');
    }

    /**
     * علاقة الطالب بقائمة المفضلة
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(\App\Models\Wishlist::class, 'user_id');
    }

    /**
     * علاقة الطالب بالتقييمات التي قام بكتابتها
     */
    public function courseReviews(): HasMany
    {
        return $this->hasMany(\App\Models\CourseReview::class, 'user_id');
    }
}