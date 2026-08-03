<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Audit trail — every admin action is recorded
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 60);              // user.ban, user.unban, user.role, message.delete, group.delete, ip.ban, ip.unban, user.permissions
            $table->string('target_type', 40)->nullable(); // user / message / group / ip
            $table->string('target_id', 60)->nullable();
            $table->string('target_label')->nullable();    // human-readable: user name, message excerpt, IP
            $table->text('details')->nullable();           // reason / old->new values
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
        });

        // Granular per-user permissions (JSON overrides; null = all defaults)
        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
