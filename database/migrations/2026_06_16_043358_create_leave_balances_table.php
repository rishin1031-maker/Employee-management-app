<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->integer('casual_total')->default(12);
            $table->integer('casual_used')->default(0);
            $table->integer('sick_total')->default(10);
            $table->integer('sick_used')->default(0);
            $table->integer('annual_total')->default(15);
            $table->integer('annual_used')->default(0);
            $table->unique(['employee_id', 'year']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};