<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->string('device_sub_type_model')->nullable()->after('recommended_action');
            $table->string('device_sub_type_title')->nullable()->after('device_sub_type_model');
            $table->string('sound_ip')->nullable()->after('device_sub_type_title');
            $table->text('display_message')->nullable()->after('sound_ip');
        });
    }

    public function down(): void
    {
        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->dropColumn(['device_sub_type_model','device_sub_type_title','sound_ip','display_message']);
        });
    }
};
