<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Privacy — these were switches in Settings that saved nothing
            $table->boolean('read_receipts')->default(true)->after('email_digest');
            $table->boolean('show_online_status')->default(true)->after('read_receipts');
            $table->boolean('show_typing')->default(true)->after('show_online_status');
            $table->string('who_can_message', 20)->default('everyone')->after('show_typing');
            // Notification preferences that were also display-only
            $table->boolean('message_previews')->default(true)->after('who_can_message');
            $table->boolean('sound_alerts')->default(true)->after('message_previews');
            // Appearance
            $table->string('font_size', 10)->default('md')->after('sound_alerts');
            $table->string('bubble_style', 12)->default('modern')->after('font_size');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'read_receipts', 'show_online_status', 'show_typing', 'who_can_message',
                'message_previews', 'sound_alerts', 'font_size', 'bubble_style',
            ]);
        });
    }
};
