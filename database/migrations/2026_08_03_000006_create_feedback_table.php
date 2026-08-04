<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);                 // bug | suggestion | question | other
            $table->text('message');
            $table->string('contact_email')->nullable(); // so guests can be replied to
            $table->string('page')->nullable();          // where they were when reporting
            $table->string('browser', 120)->nullable();  // captured automatically to help debugging
            $table->string('screen', 30)->nullable();
            $table->string('status', 20)->default('open'); // open | reviewing | resolved
            $table->text('admin_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
