<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('super_admin_connection_settings', function (Blueprint $table) {
            $table->boolean('mail_enabled')->default(false);
            $table->string('mail_host')->nullable();
            $table->unsignedSmallInteger('mail_port')->nullable();
            $table->string('mail_encryption', 10)->default('tls');
            $table->string('mail_username')->nullable();
            $table->text('mail_password')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('super_admin_connection_settings', function (Blueprint $table) {
            $table->dropColumn([
                'mail_enabled',
                'mail_host',
                'mail_port',
                'mail_encryption',
                'mail_username',
                'mail_password',
                'mail_from_address',
                'mail_from_name',
            ]);
        });
    }
};
