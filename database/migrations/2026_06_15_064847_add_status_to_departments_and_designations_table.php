<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('description');
        });

        Schema::table('designations', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('designations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};