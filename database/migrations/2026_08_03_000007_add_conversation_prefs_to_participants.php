<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            // Per-user conversation preferences — the sidebar menu changed these
            // in memory only, so every one of them was lost on reload.
            $table->boolean('is_pinned')->default(false)->after('is_muted');
            $table->boolean('is_favourite')->default(false)->after('is_pinned');
            $table->boolean('is_archived')->default(false)->after('is_favourite');
            $table->timestamp('cleared_at')->nullable()->after('is_archived');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropColumn(['is_pinned', 'is_favourite', 'is_archived', 'cleared_at']);
        });
    }
};
