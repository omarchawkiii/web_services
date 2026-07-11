<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Projector: `code` is a category code, not a per-occurrence id, so
        // (code, server_name) wrongly rejected two distinct faults sharing
        // the same code on the same screen. Re-key on id_projector_errors.
        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'location_id', 'id_projector_errors'], 'hub_projector_errors_unique_key_v2');
        });
        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->dropUnique('hub_projector_errors_unique_key');
        });

        // Storage: no reliable per-occurrence id exists at the source, and
        // `message` is a TEXT column that can't be indexed directly. Drop the
        // DB-level constraint — the per-location delete+insert sync already
        // prevents cross-sync duplication, and in-batch dedup is handled in
        // PHP (server_name + message).
        Schema::table('hub_storage_errors', function (Blueprint $table) {
            $table->index('noc_instance_id', 'hub_storage_errors_noc_instance_id_index');
        });
        Schema::table('hub_storage_errors', function (Blueprint $table) {
            $table->dropUnique('hub_storage_errors_unique_key');
        });
    }

    public function down(): void
    {
        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'location_id', 'code', 'server_name'], 'hub_projector_errors_unique_key');
        });
        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->dropUnique('hub_projector_errors_unique_key_v2');
        });

        Schema::table('hub_storage_errors', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'location_id', 'server_name'], 'hub_storage_errors_unique_key');
        });
        Schema::table('hub_storage_errors', function (Blueprint $table) {
            $table->dropIndex('hub_storage_errors_noc_instance_id_index');
        });
    }
};
