<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Authentication audit — successful logins, failures, logouts
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();          // what was typed on a failed attempt
            $table->string('event', 20);                  // success | failed | logout
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->boolean('new_device')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['event', 'created_at']);
            $table->index('ip_address');
        });

        // Periodic system health snapshots so trends are visible, not just "now"
        Schema::create('health_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('cpu_pct')->nullable();
            $table->unsignedTinyInteger('mem_pct')->nullable();
            $table->unsignedTinyInteger('disk_pct')->nullable();
            $table->unsignedInteger('pending_jobs')->default(0);
            $table->unsignedInteger('failed_jobs')->default(0);
            $table->unsignedInteger('online_users')->default(0);
            $table->unsignedInteger('messages_last_hour')->default(0);
            $table->boolean('db_ok')->default(true);
            $table->boolean('reverb_ok')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
        Schema::dropIfExists('health_snapshots');
    }
};
