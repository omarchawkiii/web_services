<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->dropColumn('date_saved');
        });

        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->string('date_saved')->nullable()->after('alarm_id');
        });
    }

    public function down(): void
    {
        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->dropColumn('date_saved');
        });

        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->bigInteger('date_saved')->nullable()->after('alarm_id');
        });
    }
};
