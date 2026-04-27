<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->foreignId('pet_id')->constrained('pets', 'pet_id')->onDelete('cascade');
            $table->foreignId('consultation_id')->constrained('consultations', 'consultation_id')->onDelete('cascade');
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('vaccination_notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
