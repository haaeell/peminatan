<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_students', function (Blueprint $table) {
            $table->foreignId('pending_class_group_id')
                ->nullable()
                ->after('is_manual_override')
                ->constrained('class_groups')
                ->nullOnDelete();

            $table->foreignId('pending_package_id')
                ->nullable()
                ->after('pending_class_group_id')
                ->constrained('packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('class_students', function (Blueprint $table) {
            $table->dropForeign(['pending_class_group_id']);
            $table->dropForeign(['pending_package_id']);
            $table->dropColumn(['pending_class_group_id', 'pending_package_id']);
        });
    }
};
