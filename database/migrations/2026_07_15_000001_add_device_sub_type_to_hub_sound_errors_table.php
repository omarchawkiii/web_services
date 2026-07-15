<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->string('device_sub_type')->nullable()->after('hardware');
        });
    }

    public function down(): void
    {
        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->dropColumn('device_sub_type');
        });
    }
};
