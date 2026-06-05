<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Vider les données corrompues
        DB::table('hub_playbacks')->truncate();
        DB::table('hub_server_alarms')->truncate();
        DB::table('hub_screens')->truncate();

        // ── hub_screens ───────────────────────────────────────────────────────
        // Étape 1 : ajouter le NOUVEL index AVANT de supprimer l'ancien
        // (MySQL a besoin d'un index sur noc_instance_id pour le FK → noc_instances.id)
        Schema::table('hub_screens', function (Blueprint $table) {
            $table->unique(
                ['noc_instance_id', 'location_id', 'noc_screen_id'],
                'hub_screens_noc_loc_screen_unique'
            );
        });

        // Étape 2 : supprimer l'ancien index (maintenant remplacé par le nouveau)
        DB::statement('ALTER TABLE hub_screens DROP INDEX hub_screens_noc_instance_id_noc_screen_id_unique');

        // ── hub_playbacks ─────────────────────────────────────────────────────
        Schema::table('hub_playbacks', function (Blueprint $table) {
            $table->unique(
                ['noc_instance_id', 'location_id', 'screen_id'],
                'hub_playbacks_noc_loc_screen_unique'
            );
        });

        DB::statement('ALTER TABLE hub_playbacks DROP INDEX hub_playbacks_noc_instance_id_screen_id_unique');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('hub_playbacks')->truncate();
        DB::table('hub_server_alarms')->truncate();
        DB::table('hub_screens')->truncate();

        Schema::table('hub_screens', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'noc_screen_id']);
        });
        DB::statement('ALTER TABLE hub_screens DROP INDEX hub_screens_noc_loc_screen_unique');

        Schema::table('hub_playbacks', function (Blueprint $table) {
            $table->unique(['noc_instance_id', 'screen_id']);
        });
        DB::statement('ALTER TABLE hub_playbacks DROP INDEX hub_playbacks_noc_loc_screen_unique');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
