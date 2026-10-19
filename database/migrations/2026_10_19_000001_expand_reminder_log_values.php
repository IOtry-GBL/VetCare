<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->string('reminder_type')->change();
            $table->string('reminder_channel')->change();
            $table->string('sent_status')->change();
        });
    }

    public function down(): void
    {
        DB::table('reminder_logs')
            ->whereNotIn('reminder_type', ['Appointment', 'Medication', 'Follow-up', 'Refill'])
            ->update(['reminder_type' => 'Appointment']);
        DB::table('reminder_logs')
            ->whereNotIn('reminder_channel', ['Email', 'SMS', 'In-system'])
            ->update(['reminder_channel' => 'In-system']);
        DB::table('reminder_logs')
            ->whereNotIn('sent_status', ['Queued', 'Sent', 'Failed'])
            ->update(['sent_status' => 'Sent']);

        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->enum('reminder_type', ['Appointment', 'Medication', 'Follow-up', 'Refill'])->change();
            $table->enum('reminder_channel', ['Email', 'SMS', 'In-system'])->change();
            $table->enum('sent_status', ['Queued', 'Sent', 'Failed'])->change();
        });
    }
};
