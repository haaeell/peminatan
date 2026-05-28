<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_package_choices', function (Blueprint $table) {
            $table->string('post_graduation_plan')->nullable()->after('second_package_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_package_choices', function (Blueprint $table) {
            $table->dropColumn('post_graduation_plan');
        });
    }
};
