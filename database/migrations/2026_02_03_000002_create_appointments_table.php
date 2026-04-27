<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
            $table->foreignId('pet_id')->constrained('pets', 'pet_id')->onDelete('cascade');
            $table->date('appointment_date')->notNullable();
            $table->time('appointment_time')->notNullable();
            $table->enum('consultation_mode', ['In-clinic', 'Teleconsultation'])->notNullable();
            $table->text('reason_for_visit')->notNullable();
            $table->enum('status', ['Pending', 'Approved', 'Rescheduled', 'Completed', 'Cancelled', 'Missed'])->notNullable();
            $table->text('proof_of_payment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
