<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            // Duration must be measured from the moment the call was answered,
            // not from when it started ringing.
            $table->timestamp('answered_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('answered_at');
        });
    }
};
