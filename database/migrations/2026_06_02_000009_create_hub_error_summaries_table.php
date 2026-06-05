<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hub_error_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->integer('kdm_errors')->default(0);
            $table->integer('nbr_sound_alert')->default(0);
            $table->integer('nbr_projector_alert')->default(0);
            $table->integer('nbr_server_alert')->default(0);
            $table->integer('nbr_storage_errors')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['noc_instance_id', 'location_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('hub_error_summaries'); }
};
