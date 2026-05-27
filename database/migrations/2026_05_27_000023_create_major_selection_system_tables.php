<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nisn')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'siswa'])->default('siswa');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nisn')->unique();
            $table->string('nis')->nullable();
            $table->string('name');
            $table->string('origin_class')->nullable(); // X A, X B, X C
            $table->enum('status', [
                'onboarding',
                'biodata',
                'package_choice',
                'selfie',
                'waiting_session',
                'academic_test',
                'psychology_test',
                'completed',
                'locked'
            ])->default('onboarding');
            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | STUDENT BIODATAS
        |--------------------------------------------------------------------------
        */
        Schema::create('student_biodatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('birth_place');
            $table->date('birth_date');
            $table->enum('gender', ['L', 'P']);
            $table->text('address');
            $table->string('phone')->nullable();
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('parent_phone');
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | STUDENT SELFIES
        |--------------------------------------------------------------------------
        */
        Schema::create('student_selfies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->json('device_info')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | TEST SESSIONS
        |--------------------------------------------------------------------------
        | Contoh:
        | Sesi 1: 07:00 - 09:00 untuk kelas X A, X B, X C
        | Sesi 2: 09:30 - 11:30 untuk kelas X D, X E, X F
        */
        Schema::create('test_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Sesi 1, Sesi 2
            $table->date('test_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('test_type', ['academic', 'psychology', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('test_session_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->constrained()->cascadeOnDelete();
            $table->string('origin_class'); // X A, X B, X C
            $table->timestamps();

            $table->unique(['test_session_id', 'origin_class']);
        });

        Schema::create('student_test_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_session_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->enum('status', [
                'not_started',
                'in_progress',
                'finished',
                'blocked'
            ])->default('not_started');
            $table->timestamps();

            $table->unique(['student_id', 'test_session_id']);
        });

        /*
        |--------------------------------------------------------------------------
        | PACKAGES / JURUSAN
        |--------------------------------------------------------------------------
        */
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#2563eb');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | PACKAGE SUBJECTS
        |--------------------------------------------------------------------------
        */
        Schema::create('package_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('subject_name');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | STUDENT PACKAGE CHOICES
        |--------------------------------------------------------------------------
        */
        Schema::create('student_package_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('first_package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('second_package_id')->constrained('packages')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('student_id');
        });

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC QUESTIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('academic_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC QUESTION OPTIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('academic_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_question_id')->constrained()->cascadeOnDelete();
            $table->string('label', 5); // A, B, C, D
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | STUDENT ACADEMIC ANSWERS
        |--------------------------------------------------------------------------
        */
        Schema::create('student_academic_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->timestamp('answered_at')->nullable();

            $table->unique(['student_id', 'academic_question_id'], 'student_academic_unique');
        });

        /*
        |--------------------------------------------------------------------------
        | PSYCHOLOGY QUESTIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('psychology_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | PSYCHOLOGY QUESTION OPTIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('psychology_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psychology_question_id')->constrained()->cascadeOnDelete();
            $table->string('label', 5); // A, B, C, D
            $table->text('option_text');
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | PSYCHOLOGY OPTION WEIGHTS
        |--------------------------------------------------------------------------
        | Satu option bisa memberi bobot ke beberapa jurusan.
        | Contoh:
        | Option A:
        | - IPA +10
        | - IPS +3
        */
        Schema::create('psychology_option_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psychology_question_option_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('package_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('weight')->default(0);

            $table->unique(
                ['psychology_question_option_id', 'package_id'],
                'psychology_weight_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | STUDENT PSYCHOLOGY ANSWERS
        |--------------------------------------------------------------------------
        */
        Schema::create('student_psychology_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('psychology_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('psychology_question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('answered_at')->nullable();

            $table->unique(['student_id', 'psychology_question_id'], 'student_psychology_unique');
        });

        /*
        |--------------------------------------------------------------------------
        | TEST RESULTS
        |--------------------------------------------------------------------------
        */
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('academic_score', 5, 2)->default(0);
            $table->json('psychology_scores')->nullable();
            $table->foreignId('recommended_package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->foreignId('final_package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | CLASS GROUPS
        |--------------------------------------------------------------------------
        | Ini kelas hasil pembagian jurusan kelas XI.
        */
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // XI IPA 1, XI IPS 1
            $table->unsignedInteger('capacity')->default(30);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | CLASS STUDENTS
        |--------------------------------------------------------------------------
        */
        Schema::create('class_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_manual_override')->default(false);
            $table->timestamps();

            $table->unique('student_id');
        });

        /*
        |--------------------------------------------------------------------------
        | ANNOUNCEMENTS
        |--------------------------------------------------------------------------
        */
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['temporary', 'final']);
            $table->string('title');
            $table->text('content')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | ANNOUNCEMENT RESPONSES
        |--------------------------------------------------------------------------
        */
        Schema::create('announcement_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('response', ['accepted', 'objected']);
            $table->timestamp('responded_at')->nullable();

            $table->unique(['announcement_id', 'student_id'], 'announcement_student_unique');
        });

        /*
        |--------------------------------------------------------------------------
        | OBJECTIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('objections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | VIOLATIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('exam_type', ['academic', 'psychology']);
            $table->string('action');
            $table->unsignedInteger('violation_count')->default(1);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('device_info')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOGS
        |--------------------------------------------------------------------------
        */
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module');
            $table->string('action');
            $table->nullableMorphs('subject');
            $table->json('properties')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | SETTINGS
        |--------------------------------------------------------------------------
        */
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('violations');
        Schema::dropIfExists('objections');
        Schema::dropIfExists('announcement_responses');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('class_groups');
        Schema::dropIfExists('test_results');
        Schema::dropIfExists('student_psychology_answers');
        Schema::dropIfExists('psychology_option_weights');
        Schema::dropIfExists('psychology_question_options');
        Schema::dropIfExists('psychology_questions');
        Schema::dropIfExists('student_academic_answers');
        Schema::dropIfExists('academic_question_options');
        Schema::dropIfExists('academic_questions');
        Schema::dropIfExists('student_package_choices');
        Schema::dropIfExists('package_subjects');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('student_test_sessions');
        Schema::dropIfExists('test_session_classes');
        Schema::dropIfExists('test_sessions');
        Schema::dropIfExists('student_selfies');
        Schema::dropIfExists('student_biodatas');
        Schema::dropIfExists('students');
        Schema::dropIfExists('users');
    }
};
