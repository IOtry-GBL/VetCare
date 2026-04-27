<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adherence_notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('adherence_id')->constrained('adherence_logs', 'adherence_id')->onDelete('cascade');
            $table->string('medication_name');
            $table->string('dosage');
            $table->dateTime('scheduled_at');
            $table->dateTime('confirmation_deadline');
            $table->enum('status', ['Pending', 'Confirmed', 'Missed', 'Deleted'])->default('Pending');
            $table->dateTime('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('confirmation_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherence_notifications');
    }
};
