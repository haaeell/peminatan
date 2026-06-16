# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 CBT (Computer-Based Test) application for high school major selection (*peminatan jurusan*). Students complete an onboarding wizard, take academic and psychology tests, and are then distributed into class groups (XI IPA 1, XI IPS 1, etc.) based on their scores.

## Commands

```bash
# Initial setup (install deps, generate key, migrate, build assets)
composer run setup

# Development (starts server, queue, log watcher, and Vite concurrently)
composer run dev

# Run all tests
composer run test

# Run a single test class or method
php artisan test --filter=ExampleTest

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Code formatting (Laravel Pint)
vendor/bin/pint

# Frontend assets
npm run dev      # dev with HMR
npm run build    # production build
```

Default DB connection is SQLite (`database/database.sqlite`). Switch to MySQL by setting `DB_CONNECTION=mysql` and related vars in `.env`.

## Architecture

### Roles and Routing

Two roles: `admin` and `siswa` (student). Route groups use `RoleMiddleware` (`role:admin` / `role:siswa`). Controllers are split under `app/Http/Controllers/Admin/` and `app/Http/Controllers/Siswa/`.

Login redirects to `RedirectController` which routes users to their respective dashboards based on role.

### Student Status Flow

`students.status` is an enum that drives the student wizard progression:

```
onboarding → biodata → package_choice → selfie → waiting_session
→ academic_test → psychology_test → completed → locked
```

The `EnsureStudentTestSessionIsOpen` middleware (`test.session.open`) guards exam routes — it checks if a `TestSession` is currently active for the student's `origin_class`, creates/updates the `student_test_sessions` pivot row, and injects `active_test_session_id` and `active_test_session_state` into request attributes.

### Test Session Caching

`TestSession::activeForOriginClass()` caches results for 15 seconds (file cache, bucket-keyed by `intdiv(now()->timestamp, 15)`) to reduce DB load during concurrent exam traffic.

### Scoring Pipeline

**Academic:** `ExamFinalizationService::finalizeAcademic()` counts correct answers, computes `(correct / total) * 100`, and writes to `test_results.academic_score`.

**Psychology:** `PsychologyScoringService::calculate()` sums `psychology_option_weights.weight` grouped by `package_id` across all of a student's answers. Highest-scoring package becomes `recommended_package_id` in `test_results`.

Submit types recorded in `student_test_sessions`: `manual`, `timeout`, or `violation`.

### Class Distribution

`ClassDistributionService::distribute()` groups students by `recommended_package_id`, sorts by `academic_score` descending, and chunks into `ClassGroup`s of 30 (XI IPA 1, XI IPA 2, etc.). Manual overrides (`is_manual_override = true`) are preserved across re-runs; auto-assigned rows are deleted and recomputed.

### Key Models and Relationships

- `User` (1:1) → `Student` (the student profile separate from auth)
- `Student` has: `biodata`, `selfie`, `packageChoice`, `academicAnswers`, `psychologyAnswers`, `result`, `classStudent`, `testSessions` (pivot), `violations`, `objections`
- `Package` = a major (e.g. IPA, IPS). Has `subjects` and `psychology_option_weights` linking options to weights per package.
- `TestSession` schedules when a class (`origin_class` string like "X A") can take the exam; linked via `test_session_classes`.
- `TestResult` holds `academic_score`, `psychology_scores` (JSON: `{package_id: total_weight}`), `recommended_package_id`, `final_package_id`, `is_locked`.
- `Announcement` (type: `temporary`/`final`) → students respond via `AnnouncementResponse` (accepted/objected); objections go to `Objection` model with admin review.

### Services

| Service | Responsibility |
|---|---|
| `ExamFinalizationService` | Score academic exam, update session pivot |
| `PsychologyScoringService` | Calculate weighted psychology scores |
| `ClassDistributionService` | Auto/manual student-to-class assignment |
| `ActivityLogService` | Polymorphic admin action logging |
| `MathTextService` | Render math notation in question text |
| `QuestionImageService` | Handle image uploads for questions |

### Excel Import/Export

Uses `maatwebsite/excel`. Import/Export classes live in `app/Imports/` and `app/Exports/`. Supported: students, academic questions, psychology questions (with template downloads).

### PDF Reports

Uses `barryvdh/laravel-dompdf` via `ReportController`. Report views in `resources/views/admin/reports/`.

### DataTables

Uses `yajra/laravel-datatables-oracle` for server-side DataTables. Controllers expose a `/data` route returning JSON (e.g. `students.data`, `test-results.data`).
