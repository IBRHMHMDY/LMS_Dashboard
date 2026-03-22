# 🎓 LMS Platform Backend - Enterprise API & Dashboard

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Filament](https://img.shields.io/badge/Filament_v4-ECA824?style=for-the-badge&logo=filament&logoColor=white)
![Sanctum](https://img.shields.io/badge/Sanctum_Auth-4A4A55?style=for-the-badge)

An enterprise-grade Learning Management System (LMS) backend built with **Laravel**. It provides a highly scalable RESTful API specifically designed for seamless integration with a **Clean Architecture Flutter mobile application**, alongside powerful administrative and instructor dashboards powered by **Filament PHP**.

## 🏗 Architectural Highlights

This project was built with a strict adherence to **SOLID principles** and **Clean Code** standards, avoiding "fat controllers" and ensuring high maintainability:

* **Service Layer Pattern:** Business logic (e.g., complex IAP enrollments, review validations) is decoupled from controllers and encapsulated in dedicated Service classes (`EnrollmentService`, `CourseReviewService`).
* **Standardized API Responses:** Utilizes a global `ApiResponse` trait ensuring all endpoints return a predictable and unified JSON structure `{"success", "message", "data", "errors"}`.
* **API Resources:** Strict use of Eloquent API Resources (`CourseResource`, `LessonDetailResource`) for data transformation, boolean casting, and eager loading to prevent N+1 query problems.
* **Form Request Validation:** 100% of incoming data validations are handled via dedicated FormRequests.
* **Enum-Driven Logic:** Extensively uses PHP Enums (`CourseLevel`, `LessonType`, `TransactionStatus`) for strict typing and state management.

## ✨ Key Features & Modules

### 1. 🔐 Authentication & Authorization
* Token-based authentication via **Laravel Sanctum**.
* Role-Based Access Control (RBAC) using **Spatie Permissions** (Admin, Instructor, Student).
* Secure profile and password management.

### 2. 📚 Course Management
* Hierarchical data structure: Categories ➔ Courses ➔ Sections ➔ Lessons.
* Support for multiple lesson types (Video URLs, Text, Documents).
* Dynamic filtering, pagination, and search capabilities.

### 3. 💳 Checkout & In-App Purchases (IAP)
* **Advanced Transaction Module:** Designed to handle split payments (Platform Commission vs. Instructor Earnings).
* **Apple & Google IAP Integration:** Dedicated endpoints to securely verify and process mobile store receipts and automatically enroll users.
* Future-proof gateway architecture (ready for Stripe/Paymob).

### 4. 🎥 Mobile-Optimized Learning Experience
* **Watch-Time Tracking:** Real-time synchronization of video watch time (`watch_time_in_seconds`) for cross-session resume capability.
* Automated lesson completion toggling.
* Course wishlisting and dynamic course reviews with business-rule validations (must be enrolled to review).

### 5. 🔔 Engagement & Notifications
* Database-driven notification system.
* Read/Unread state management tailored for mobile notification badges.

## 🗄 Database Schema Snapshot

A robust relational database with complete referential integrity, cascading deletes, and strict unique constraints (e.g., preventing duplicate wishlists or multiple reviews per user). 
*Includes advanced seeding strategies (`DatabaseSeeder`) simulating real-world scenarios with comprehensive mock data for IAP transactions and user roles.*

## 📡 API Contract Example

All endpoints follow this strict unified structure. This makes writing network interceptors on the mobile side (e.g., Flutter Dio) incredibly efficient.

**Success Response (200 OK)**
```json
{
  "success": true,
  "message": "Progress synced successfully.",
  "data": {
    "watch_time_in_seconds": 125,
    "is_completed": false
  }
}
```
**Validation Error Response (422 Unprocessable Entity)**

```JSON
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "rating": ["The rating must be between 1 and 5."]
  }
}
```
## 🚀 Quick Start (Development)
1. Clone the repository.

2. Install PHP dependencies: composer install

3. Copy .env.example to .env and set up your database credentials.

4. Generate app key: php artisan key:generate

5. Run the master migration and seeder (Populates roles, users, courses, and IAP transactions):

```bash
php artisan migrate:fresh --seed
Install and configure API routing:
```
```bash
php artisan install:api
```
Start the local server: 
```bash 
php artisan serve
```

**Architected and developed with a focus on scalability and mobile-first integration.**