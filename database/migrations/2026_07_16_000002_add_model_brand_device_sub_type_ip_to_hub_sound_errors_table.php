<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->string('model')->nullable()->after('sound_ip');
            $table->string('brand')->nullable()->after('model');
            $table->string('device_sub_type_ip')->nullable()->after('brand');
        });
    }

    public function down(): void
    {
        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->dropColumn(['model', 'brand', 'device_sub_type_ip']);
        });
    }
};
