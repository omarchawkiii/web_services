<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_server_errors', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'location_id', 'event_id'], 'hub_server_errors_unique_key');
        });

        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'location_id', 'code', 'server_name'], 'hub_projector_errors_unique_key');
        });

        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'location_id', 'alarm_id'], 'hub_sound_errors_unique_key');
        });

        Schema::table('hub_storage_errors', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'location_id', 'server_name'], 'hub_storage_errors_unique_key');
        });

        Schema::table('hub_tms_errors', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'location_id', 'id_tms_error'], 'hub_tms_errors_unique_key');
        });
    }

    public function down(): void
    {
        Schema::table('hub_server_errors', function (Blueprint $table) {
            $table->dropUnique('hub_server_errors_unique_key');
        });

        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->dropUnique('hub_projector_errors_unique_key');
        });

        Schema::table('hub_sound_errors', function (Blueprint $table) {
            $table->dropUnique('hub_sound_errors_unique_key');
        });

        Schema::table('hub_storage_errors', function (Blueprint $table) {
            $table->dropUnique('hub_storage_errors_unique_key');
        });

        Schema::table('hub_tms_errors', function (Blueprint $table) {
            $table->dropUnique('hub_tms_errors_unique_key');
        });
    }
};
