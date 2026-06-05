<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hub_playbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('screen_id')->constrained('hub_screens')->cascadeOnDelete();
            $table->string('playback_status')->default('Unknown');
            $table->string('spl_title')->nullable();
            $table->string('cpl_title')->nullable();
            $table->string('elapsed_runtime')->nullable();
            $table->string('remaining_runtime')->nullable();
            $table->decimal('progress_bar', 5, 2)->nullable();
            $table->tinyInteger('projector_status')->nullable();
            $table->string('projector_lamp_stat')->nullable();
            $table->string('lamp_status')->nullable();
            $table->string('dowser_status')->nullable();
            $table->string('ip_management_server_status')->nullable();
            $table->string('storage_generale_status')->nullable();
            $table->string('schedule_mode')->nullable();
            $table->string('security_manager')->nullable();
            $table->string('soap_session')->nullable();
            $table->string('sound_model')->nullable();
            $table->tinyInteger('ip_sound_status')->nullable();
            $table->string('mute_status')->nullable();
            $table->string('fader_status')->nullable();
            $table->string('format_status')->nullable();
            $table->string('bit_stream')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['noc_instance_id', 'screen_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('hub_playbacks'); }
};
