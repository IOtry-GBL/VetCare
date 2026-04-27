<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adherence_logs', function (Blueprint $table) {
            $table->dateTime('confirmation_deadline')->nullable()->after('scheduled_datetime');
            $table->boolean('is_notified')->default(false)->after('confirmation_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('adherence_logs', function (Blueprint $table) {
            $table->dropColumn(['confirmation_deadline', 'is_notified']);
        });
    }
};
