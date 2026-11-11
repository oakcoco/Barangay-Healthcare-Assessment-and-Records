Barangay Health Care System
Community Assessment and Records System for the Municipality of San Ildefonso.
A mobile-first web application that helps Barangay Health Workers (BHWs) keep resident health records, and lets residents see their own records and community announcements. This repository is a presentation demo of the web version. An offline-capable mobile app is planned for the main deployment (see Roadmap).
> **Data notice:** the demo uses invented people only. Never commit real resident data, photos or database exports to this repository.
---
Contents
Roles and features
Technology stack
Requirements
Installation
Demo accounts
Pages
Database
Security
Project structure
Development method
Roadmap
Team
---
Roles and features
Role	What they can do
Resident	Sign up, log in, see upcoming events and announcements, view their own health card (read-only) and the history of BHWs assigned to them, view their profile
Barangay Health Worker (BHW)	Approve or reject resident sign-ups in their barangay, post, edit and delete events and announcements, see their assigned residents, record health data for each resident (see below), move a resident to another barangay
Mother Leader (one account)	Approve BHW and resident sign-ups, assign BHWs to residents, view simple statistics, post events. Front end only for now.
Health data a BHW can record for a resident: citizen profile (name, birthdate, sex, address, contact number), vital signs (blood pressure, temperature, oxygen saturation, respiratory rate, pulse rate, CBG), nutrition (height, weight, calculated BMI), lab scores, medications, socioeconomic details and social services, patient photo and photo evidence.
Status
Area	Status
Sign-up (two steps), login, approval check, logout	Done
Role-based page protection	Done
Resident home, referrals history, profile	Done
BHW dashboard, events (post, edit, delete), sign-up approval	Done
My residents list, edit record (profile, vital signs, nutrition, scores, medications), change barangay	Done
Socioeconomic details and photo saving	In progress
Mother Leader dashboard	Front end only (placeholder data)
JSON API, offline mobile app, sync	Planned
---
Technology stack
Layer	Technology
Server framework	Laravel 12 (PHP 8.2)
Web front end	Blade templates, Tailwind CSS 4, Vite, Heroicons
Database	MySQL / MariaDB (XAMPP)
Auth	Laravel session authentication, custom role middleware
Planned mobile app	React Native with Expo, encrypted SQLite on the phone, Laravel Sanctum
---
Requirements
PHP 8.2 or newer
Composer
Node.js and npm
XAMPP (or any setup with MySQL 8.0.16+ / MariaDB 10.2.5+, which the schema needs for `CHECK` constraints and stored generated columns)
Git
---
Installation
```bash
git clone https://github.com/<owner>/Barangay-Healthcare-Assessment-and-Records.git
cd Barangay-Healthcare-Assessment-and-Records

composer install
npm install

copy .env.example .env      # on macOS or Linux: cp .env.example .env
php artisan key:generate
```
1. Create the database
Start Apache and MySQL in XAMPP, open `http://localhost/phpmyadmin`, choose Import, and select:
```
database/sql/barangay_health_schema.sql
```
It creates the `barangay_health` database, all 15 tables and the starter lookup data. If you are rebuilding, drop the old `barangay_health` database first.
> Do **not** run `php artisan migrate`. The schema file replaces Laravel's default `users` table.
2. Configure `.env`
Edit the existing lines instead of adding duplicates:
```dotenv
APP_NAME=BarangayHealth
APP_TIMEZONE=Asia/Manila

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=barangay_health
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_COOKIE=barangay_health_session
CACHE_STORE=file
QUEUE_CONNECTION=sync

# Password for the seeded demo accounts (at least 8 characters)
DEMO_PASSWORD=
```
`.env` is ignored by Git. Never commit it.
3. Photo upload limits (optional)
PHP's defaults are too small for several photos at once. Find your `php.ini` with `php --ini` and set:
```ini
upload_max_filesize=4M
post_max_size=24M
max_file_uploads=20
```
Restart `php artisan serve` afterwards.
4. Load the demo data and run
```bash
php artisan config:clear
php artisan db:seed
```
Then, in two terminals:
```bash
php artisan serve
npm run dev
```
Open `http://localhost:8000/login`.
For a production-style build without the dev server: `npm run build`, and set `APP_DEBUG=false` in `.env`.
---
Demo accounts
Created by `database/seeders/DemoSeeder.php`. The password for every account is the `DEMO_PASSWORD` value in your `.env`.
Phone	Account
`09170000001`	Demo BHW (Barangay 1)
`09170000004`	Former BHW (appears as a past assignment)
`09170000002`	Demo Resident (has sample readings and medications)
`09170000003`	Pending Resident (waiting for approval)
The seeder is safe to run twice, but it does not reset existing accounts. For a clean reset, re-import the SQL file and seed again. The seeded event is dated one week after the day you seed, so reseed before presenting.
The Mother Leader account is created by hand and is never available through sign-up:
```bash
php artisan tinker
>>> App\Models\User::create(['name' => 'Mother Leader', 'phone' => '09170000100', 'password' => 'USE-A-STRONG-PASSWORD', 'role' => 'mother_leader', 'status' => 'approved', 'approved_at' => now()]);
```
---
Pages
Area	Address
Public	`/login`, `/register`, `/register/step-2`
Resident	`/resident/home`, `/resident/referrals`, `/resident/profile`
BHW	`/bhw/dashboard`, `/bhw/residents`, `/bhw/records/{id}/edit`, `/bhw/events/create`, `/bhw/events/{id}/edit`, `/bhw/profile`
Mother Leader	`/leader/dashboard` (front end only)
New accounts start as pending. A pending account cannot log in until it is approved. Until the Mother Leader backend exists, BHW accounts are approved in the database (`users.status = 'approved'`).
---
Database
15 tables, defined in `database/sql/barangay_health_schema.sql`:
People and places: `barangays`, `users`, `bhw_profiles`, `residents`, `resident_residences`, `bhw_assignments`
Community: `events`
Resident records: `socioeconomic_profiles`, `social_services`, `resident_social_services`, `vital_signs`, `nutritional_records`, `medical_scores`, `medications`, `record_photos`
Design notes:
One account is one person. A resident record can exist without an account (`residents.user_id` is optional).
The database enforces valid ranges (for example oxygen saturation 0 to 100) and calculates BMI itself.
`resident_residences` keeps the address history when a resident moves, and `bhw_assignments` keeps the history of assigned BHWs. A unique key allows only one current row per resident.
Medications, scores and photos are hidden with `deleted_at`, not erased.
---
Security
All `/resident/*`, `/bhw/*` and `/leader/*` pages require login. The `EnsureRole` middleware checks that the account's role matches the area of the address, and that the account is approved.
A BHW can open only residents currently assigned to them. Other ids return a 404 page.
Sign-up only offers the Resident and Healthcare Worker roles, and the server rejects any other role.
Passwords are stored only as hashes.
Uploaded photos are stored privately in `storage/app/private` and served through a route that checks the assignment. They are not in `public/`, and Git ignores them.
`.env`, `vendor/` and `node_modules/` are never committed.
Because this system stores health information, review the Data Privacy Act requirements (data location, registration, data protection officer) with the adviser or the municipality before any real deployment.
---
Project structure
```
app/Http/Controllers/AuthController.php   sign-up, login, logout
app/Http/Middleware/EnsureRole.php        login and role checks
app/Models/                               User, Resident, BhwProfile, Barangay
database/sql/barangay_health_schema.sql   MySQL schema and starter data
database/seeders/DemoSeeder.php           demo accounts and sample records
resources/views/login, register*.blade.php
resources/views/resident/                 resident pages
resources/views/bhw/                      BHW pages
resources/views/leader/                   Mother Leader dashboard
routes/web.php                            all routes
```
---
Development method
Agile (iterative and incremental) with prototyping. Screens were designed first, then built and tested one feature at a time, with the riskiest part, offline sync, to be proven with a small proof of concept before the full mobile app is built.
---
Roadmap
Mother Leader backend: approvals, BHW assignment, statistics and event posting
JSON API with Laravel Sanctum
Mobile proof of concept: log in, download a resident, record a reading in airplane mode, then sync it
Mobile app (React Native with Expo) with offline recording and sync. Each offline record gets a UUID, so syncing twice cannot create duplicates.
Pilot with a few BHWs on a cloud server with HTTPS, using Google Play internal testing
Data Privacy Act review before any real deployment
---


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
