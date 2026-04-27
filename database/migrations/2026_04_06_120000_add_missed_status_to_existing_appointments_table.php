<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement(
            "ALTER TABLE appointments MODIFY status ENUM('Pending', 'Approved', 'Rescheduled', 'Completed', 'Cancelled', 'Missed') NOT NULL"
        );
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::table('appointments')
            ->where('status', 'Missed')
            ->update(['status' => 'Cancelled']);

        DB::statement(
            "ALTER TABLE appointments MODIFY status ENUM('Pending', 'Approved', 'Rescheduled', 'Completed', 'Cancelled') NOT NULL"
        );
    }
};
