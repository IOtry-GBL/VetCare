<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_prescriptions', function (Blueprint $table) {
            $table->id('prescription_id');
            $table->foreignId('record_id')->constrained('medical_records', 'record_id')->onDelete('cascade');
            $table->string('medication_name', 100)->notNullable();
            $table->string('dosage', 50)->notNullable();
            $table->string('frequency', 50)->notNullable();
            $table->string('duration', 50)->notNullable();
            $table->dateTime('issued_at')->notNullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_prescriptions');
    }
};
