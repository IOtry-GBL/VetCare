<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_admin_connection_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('database_driver', 20)->default('mysql');
            $table->string('database_host');
            $table->unsignedSmallInteger('database_port');
            $table->string('database_name');
            $table->string('database_username');
            $table->text('database_password')->nullable();
            $table->text('ollama_base_url')->nullable();
            $table->text('ollama_api_key')->nullable();
            $table->string('ollama_model')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_admin_connection_settings');
    }
};
