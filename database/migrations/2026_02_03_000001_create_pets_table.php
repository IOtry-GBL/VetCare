<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id('pet_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->string('pet_name', 80)->notNullable();
            $table->string('species', 50)->notNullable();
            $table->string('breed', 80)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('sex', 20)->notNullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
