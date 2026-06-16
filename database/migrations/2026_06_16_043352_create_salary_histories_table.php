<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salary_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('salary_id')->constrained()->onDelete('cascade');
            $table->decimal('basic', 10, 2);
            $table->decimal('hra', 10, 2);
            $table->decimal('transport', 10, 2);
            $table->decimal('medical', 10, 2);
            $table->decimal('pf_deduction', 10, 2);
            $table->decimal('tax_deduction', 10, 2);
            $table->decimal('other_allowance', 10, 2);
            $table->decimal('other_deduction', 10, 2);
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->date('effective_from');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_histories');
    }
};