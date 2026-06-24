<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // break_out must NOT auto-update on row changes — it stores break START time.
        // Without this fix, endBreak() sets break_in = now() and MySQL also overwrites
        // break_out to now(), making both timestamps identical (0-duration breaks).
        DB::statement('ALTER TABLE attendance_breaks MODIFY break_out TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE attendance_breaks MODIFY break_out TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
};
