<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dedupe('hub_server_errors', ['noc_instance_id', 'location_id', 'event_id']);
        $this->dedupe('hub_projector_errors', ['noc_instance_id', 'location_id', 'code', 'server_name']);
        $this->dedupe('hub_sound_errors', ['noc_instance_id', 'location_id', 'alarm_id']);
        $this->dedupe('hub_storage_errors', ['noc_instance_id', 'location_id', 'server_name']);
        $this->dedupe('hub_tms_errors', ['noc_instance_id', 'location_id', 'id_tms_error']);

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

    /**
     * Keep only the most recent row (highest id) per key combination, so the
     * unique index can be created. NULL key columns never collide in MySQL,
     * so groups containing NULL are left untouched (nothing to dedupe there).
     */
    private function dedupe(string $table, array $keyColumns): void
    {
        $keys = implode(', ', $keyColumns);

        DB::statement("
            DELETE t1 FROM {$table} t1
            INNER JOIN {$table} t2
              ON t1.id < t2.id
              AND " . implode(' AND ', array_map(fn($c) => "t1.{$c} = t2.{$c}", $keyColumns)) . "
        ");
    }
};
