<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('system_settings')->insert([
            [
                'key' => 'continuous_session_enabled',
                'value' => '1',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'continuous_session_limit_minutes',
                'value' => '465', // 7h 45m
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'continuous_session_reminder_before_minutes',
                'value' => '15',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'continuous_session_grace_minutes',
                'value' => '5',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'continuous_session_min_break_minutes',
                'value' => '2',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('continuous_reminder_sent_at')->nullable()->after('note');
            $table->timestamp('continuous_session_anchor_at')->nullable()->after('continuous_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['continuous_reminder_sent_at', 'continuous_session_anchor_at']);
        });

        Schema::dropIfExists('system_settings');
    }
};
