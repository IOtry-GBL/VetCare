<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id('reminder_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('reminder_type', ['Appointment', 'Medication', 'Follow-up', 'Refill'])->notNullable();
            $table->enum('reminder_channel', ['Email', 'SMS', 'In-system'])->notNullable();
            $table->dateTime('scheduled_at')->notNullable();
            $table->enum('sent_status', ['Queued', 'Sent', 'Failed'])->notNullable();
            $table->string('related_reference', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
