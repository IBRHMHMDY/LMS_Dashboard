<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء الصلاحيات (Roles)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        // 2. إنشاء المستخدمين الأساسيين
        $admin = User::firstOrCreate(
            ['email' => 'admin@lms.com'],
            ['name' => 'System Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $admin->assignRole($adminRole);

        $instructor = User::firstOrCreate(
            ['email' => 'instructor@lms.com'],
            ['name' => 'John Doe (Instructor)', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $instructor->assignRole($instructorRole);

        $student = User::firstOrCreate(
            ['email' => 'student@lms.com'],
            ['name' => 'Jane Smith (Student)', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $student->assignRole($studentRole);

        // 3. إنشاء الأقسام
        $programmingCategory = Category::firstOrCreate(
            ['slug' => 'programming'],
            ['name' => 'Programming', 'is_active' => true]
        );
        $designCategory = Category::firstOrCreate(
            ['slug' => 'design'],
            ['name' => 'Design', 'is_active' => true]
        );

        // 4. إنشاء كورس وهمي
        $course = Course::firstOrCreate(
            ['slug' => 'flutter-masterclass-101'],
            [
                'instructor_id' => $instructor->id,
                'category_id' => $programmingCategory->id,
                'title' => 'Flutter Masterclass 101',
                'subtitle' => 'Learn Flutter from scratch to advanced',
                'description' => '<p>This is a complete guide to learning Flutter.</p>',
                'price' => 49.99,
                'level' => 'beginner',
                'status' => 'published',
            ]
        );

        // 5. إنشاء وحدات ودروس للكورس
        if ($course->sections()->count() === 0) {
            $section1 = Section::create(['course_id' => $course->id, 'title' => 'Getting Started', 'order' => 1]);
            
            Lesson::create([
                'section_id' => $section1->id,
                'title' => 'Introduction to Flutter',
                'slug' => 'intro-to-flutter-' . Str::random(5),
                'lesson_type' => 'video_url',
                'video_url' => 'https://www.youtube.com/watch?v=1gDhl4leEzA',
                'duration_in_minutes' => 10,
                'is_free_preview' => true,
                'order' => 1,
            ]);

            Lesson::create([
                'section_id' => $section1->id,
                'title' => 'Installing the SDK',
                'slug' => 'installing-sdk-' . Str::random(5),
                'lesson_type' => 'text',
                'content' => '<p>Follow the official docs to install Flutter SDK.</p>',
                'duration_in_minutes' => 15,
                'is_free_preview' => false,
                'order' => 2,
            ]);
        }

        $this->command->info('Database seeded successfully! Credentials: admin@lms.com / password');
    }
}