<?php

namespace App\Models;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'phone_number',
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
        ];
    }

    // علاقة المدرب بكورساته
    public function coursesAsInstructor(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    // سيتم إضافة علاقة الـ enrollments لاحقاً عند إنشاء الـ Model
}