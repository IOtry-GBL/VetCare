<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->decimal('weight', 6, 2)->nullable()->after('diagnosis');
            $table->decimal('temp', 5, 2)->nullable()->after('weight');
            $table->text('food')->nullable()->after('temp');
            $table->text('environment')->nullable()->after('food');
            $table->decimal('fee', 10, 2)->nullable()->after('environment');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn(['weight', 'temp', 'food', 'environment', 'fee']);
        });
    }
};
