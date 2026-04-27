<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adherence_logs', function (Blueprint $table) {
            $table->id('adherence_id');
            $table->foreignId('prescription_id')->constrained('e_prescriptions', 'prescription_id')->onDelete('cascade');
            $table->dateTime('scheduled_datetime')->notNullable();
            $table->enum('intake_status', ['Taken', 'Missed', 'Pending', 'Delayed'])->notNullable();
            $table->dateTime('confirmation_time')->nullable();
            $table->string('remarks', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherence_logs');
    }
};
