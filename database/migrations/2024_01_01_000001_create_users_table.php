<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('username')->unique();
            $table->string('avatar')->nullable();
            $table->string('bio', 160)->nullable();
            $table->string('status_message', 100)->nullable();
            $table->enum('status_type', ['available', 'busy', 'away'])->default('available');
            $table->string('status_emoji', 10)->nullable();
            $table->timestamp('status_clears_at')->nullable();
            $table->enum('role', ['admin', 'user', 'guest'])->default('user');
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->timestamp('banned_at')->nullable();
            $table->string('banned_reason')->nullable();
            $table->boolean('dark_mode')->default(false);
            $table->boolean('email_notifications')->default(true);
            $table->enum('email_digest', ['never', 'daily', 'weekly'])->default('daily');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
