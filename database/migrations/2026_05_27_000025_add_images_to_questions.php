<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_questions', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('question');
        });

        Schema::table('psychology_questions', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('question');
        });
    }

    public function down(): void
    {
        Schema::table('psychology_questions', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('academic_questions', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
