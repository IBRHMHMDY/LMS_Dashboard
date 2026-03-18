<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\PayoutRequest;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Comprehensive Database Seeding... 🚀');

        // 1. إعدادات النظام
        Setting::firstOrCreate(
            ['key' => 'platform_commission_percentage'],
            ['type' => 'integer', 'group' => 'financials', 'value' => '20']
        );

        // 2. الصلاحيات
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // 3. إنشاء مدراء
        $admins = [
            User::firstOrCreate(['email' => 'admin@lms.com'], ['name' => 'System Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()])->assignRole($adminRole),
            User::firstOrCreate(['email' => 'manager@lms.com'], ['name' => 'Financial Manager', 'password' => Hash::make('password'), 'email_verified_at' => now()])->assignRole($adminRole),
        ];

        // 4. إنشاء مدربين
        $instructors = [
            User::firstOrCreate(['email' => 'ahmed@lms.com'], ['name' => 'Ahmed Ali', 'password' => Hash::make('password'), 'email_verified_at' => now()])->assignRole($instructorRole),
            User::firstOrCreate(['email' => 'sarah@lms.com'], ['name' => 'Sarah Smith', 'password' => Hash::make('password'), 'email_verified_at' => now()])->assignRole($instructorRole),
            User::firstOrCreate(['email' => 'john@lms.com'], ['name' => 'John Doe', 'password' => Hash::make('password'), 'email_verified_at' => now()])->assignRole($instructorRole),
        ];

        // 5. إنشاء 5 طلاب
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $students[] = User::firstOrCreate(['email' => "student{$i}@lms.com"], [
                'name' => "Student Number {$i}",
                'password' => Hash::make('password'),
                'email_verified_at' => now()
            ])->assignRole($studentRole);
        }

        // 6. الأقسام
        $categories = [
            Category::firstOrCreate(['slug' => 'web-development'], ['name' => 'Web Development', 'is_active' => true]),
            Category::firstOrCreate(['slug' => 'ui-ux-design'], ['name' => 'UI/UX Design', 'is_active' => true]),
            Category::firstOrCreate(['slug' => 'mobile-development'], ['name' => 'Mobile Development', 'is_active' => true]),
        ];

        // 7. بناء الكورسات
        $coursesData = [
            ['title' => 'Laravel 11 Advanced', 'price' => 100, 'status' => 'published', 'cat' => 0, 'inst' => 0],
            ['title' => 'Vue.js 3 Basics', 'price' => 50, 'status' => 'published', 'cat' => 0, 'inst' => 0],
            ['title' => 'Figma Masterclass', 'price' => 80, 'status' => 'published', 'cat' => 1, 'inst' => 1],
            ['title' => 'Flutter Clean Architecture', 'price' => 120, 'status' => 'published', 'cat' => 2, 'inst' => 2],
            ['title' => 'Dart for Beginners', 'price' => 0, 'status' => 'published', 'cat' => 2, 'inst' => 2],
            ['title' => 'React Native (Pending)', 'price' => 90, 'status' => 'pending', 'cat' => 2, 'inst' => 2],
        ];

        $createdCourses = [];
        foreach ($coursesData as $data) {
            $course = Course::firstOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'title' => $data['title'],
                    'subtitle' => 'Comprehensive guide for ' . $data['title'],
                    'description' => '<p>Detailed description for the course goes here.</p>',
                    'price' => $data['price'],
                    'level' => 'intermediate',
                    'status' => $data['status'],
                    'instructor_id' => $instructors[$data['inst']]->id,
                    'category_id' => $categories[$data['cat']]->id,
                    'created_at' => Carbon::now()->subDays(rand(30, 90)),
                ]
            );

            if ($data['status'] === 'published') {
                $createdCourses[] = $course;
                $section1 = Section::firstOrCreate(['course_id' => $course->id, 'title' => 'Basics', 'order' => 1]);
                $section2 = Section::firstOrCreate(['course_id' => $course->id, 'title' => 'Advanced', 'order' => 2]);

                for ($i = 1; $i <= 10; $i++) {
                    $lessonType = $i % 3 === 0 ? 'text' : 'video_url';
                    Lesson::firstOrCreate(
                        ['slug' => Str::slug($course->title . " lesson $i " . uniqid())],
                        [
                            'section_id' => $i <= 5 ? $section1->id : $section2->id,
                            'title' => $lessonType === 'video_url' ? "Video: Topic $i" : "Reading $i",
                            'lesson_type' => $lessonType,
                            'video_url' => $lessonType === 'video_url' ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
                            'content' => $lessonType === 'text' ? '<p>Lesson text content.</p>' : null,
                            'duration_in_minutes' => rand(5, 15),
                            'is_free_preview' => ($i === 1),
                            'order' => $i <= 5 ? $i : ($i - 5),
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        $this->command->info('Creating Enrollments, Transactions, and Progress...');

        // 8. تسجيل الطلاب والمدفوعات
        foreach ($students as $student) {
            $randomCourses = collect($createdCourses)->random(rand(2, 4));
            
            foreach ($randomCourses as $course) {
                $enrollDate = Carbon::now()->subDays(rand(1, 60));

                if ($course->price > 0) {
                    $commission = $course->price * 0.20;
                    $earning = $course->price - $commission;

                    Transaction::create([
                        'user_id' => $student->id,
                        'instructor_id' => $course->instructor_id,
                        'course_id' => $course->id,
                        'transaction_number' => 'TRX-' . strtoupper(Str::random(12)),
                        'amount' => $course->price,
                        'platform_commission' => $commission,
                        'instructor_earning' => $earning,
                        'status' => 'completed',
                        'payment_gateway' => 'mock_card',
                        'payment_gateway_reference' => 'ref_' . Str::random(8),
                        'created_at' => $enrollDate,
                        'updated_at' => $enrollDate,
                    ]);
                }

                Enrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'is_active' => true,
                    'enrolled_at' => $enrollDate,
                    'created_at' => $enrollDate,
                    'updated_at' => $enrollDate,
                ]);

                // التعديل الآمن: جلب الدروس عبر الاستعلام المباشر من قاعدة البيانات
                $sectionIds = Section::where('course_id', $course->id)->pluck('id');
                $lessons = Lesson::whereIn('section_id', $sectionIds)->get();
                
                $lessonsToComplete = $lessons->take(rand(2, 8));
                
                foreach ($lessonsToComplete as $lesson) {
                    LessonProgress::updateOrCreate(
                        ['user_id' => $student->id, 'lesson_id' => $lesson->id],
                        ['completed_at' => (clone $enrollDate)->addDays(rand(1, 5))]
                    );
                }
            }
        }

        $this->command->info('Creating Payout Requests for Instructors...');

        // 9. طلبات السحب
        foreach ($instructors as $instructor) {
            $totalEarned = Transaction::where('instructor_id', $instructor->id)->sum('instructor_earning');

            if ($totalEarned > 50) {
                PayoutRequest::create([
                    'user_id' => $instructor->id,
                    'amount' => 50,
                    'status' => 'paid',
                    'instructor_notes' => 'PayPal: ' . $instructor->email,
                    'admin_notes' => 'Transferred via PayPal. TxID: ' . strtoupper(Str::random(10)),
                    'created_at' => Carbon::now()->subDays(15),
                    'processed_at' => Carbon::now()->subDays(14),
                ]);

                $remaining = $totalEarned - 50;
                if ($remaining > 10) {
                    PayoutRequest::create([
                        'user_id' => $instructor->id,
                        'amount' => $remaining,
                        'status' => 'pending',
                        'instructor_notes' => 'PayPal: ' . $instructor->email,
                        'created_at' => Carbon::now()->subDays(2),
                    ]);
                }
            }
        }

        $this->command->info('✅ Master Seeding Completed Successfully!');
    }
}