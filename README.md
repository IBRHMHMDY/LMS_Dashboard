# 🚀 LMS Backend & Dashboard (Laravel 12 + Filament 3)

A robust, scalable, and highly optimized Backend and Admin/Instructor Dashboard for an E-Learning platform, built as the foundation for a mobile application.

## 🌟 Key Features
- **Clean Architecture:** Service classes, API Resources, and Form Requests.
- **Multi-Panel Dashboards:** - Admin Panel (Manage users, financials, content approval).
  - Instructor Panel (Course builder, modal-based lesson management, payouts).
- **Secure RESTful APIs:** Versioned (`v1`) APIs protected by Laravel Sanctum.
- **Smart Lesson Types:** Supports Video URL, Video Uploads, PDFs, and Rich Text (powered by Spatie Media Library).
- **Financial Engine:** Automated commission calculation and mock transaction system.

## 🛠️ Tech Stack
- **Framework:** Laravel 11.x
- **Admin Panel:** FilamentPHP v3
- **Authentication:** Laravel Sanctum
- **Roles & Permissions:** Spatie Permission
- **Media Management:** Spatie Media Library

## 🚀 Getting Started

### 1. Clone the repository
```bash
git clone https://github.com/IBRHMHMDY/LMS_Dashboard.git
cd lms-dashboard
```
2. Install Dependencies
Bash```
composer install
```
3. Environment Setup
Bash
cp .env.example .env
php artisan key:generate
```
(Update your .env file with your database credentials).

4. Run Migrations & Seed Dummy Data
Bash
php artisan migrate:fresh --seed
```
This will create the necessary roles and dummy accounts.

5. Link Storage (Crucial for Images/Videos)
Bash
php artisan storage:link
```
6. Serve the Application
```bash
php artisan serve
```
Default Credentials
--------------

```bash
Admin: admin@lms.com / password -> 
```
Access at /admin

```bash
Instructor: instructor@lms.com / password
```
Access at /instructor

📱 API Endpoints
All APIs are prefixed with /api/v1/. For detailed endpoints, please refer to the internal documentation.

