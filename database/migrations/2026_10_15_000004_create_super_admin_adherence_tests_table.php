<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_admin_adherence_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_email');
            $table->string('medication_name')->default('Test medication');
            $table->string('dosage')->default('Test dosage');
            $table->string('token_hash', 64)->unique();
            $table->string('status', 20)->default('Sent');
            $table->dateTime('expires_at');
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_admin_adherence_tests');
    }
};
