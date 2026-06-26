<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hub_tms_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('id_tms_error')->nullable();
            $table->string('title')->nullable();
            $table->string('code')->nullable();
            $table->string('severity')->nullable();
            $table->text('message')->nullable();
            $table->string('time_saved')->nullable();
            $table->string('id_screen')->nullable();
            $table->string('ip_projector')->nullable();
            $table->text('display_message')->nullable();
            $table->text('recommended_action')->nullable();
            $table->string('device_sub_type')->nullable();
            $table->string('device_sub_type_ip')->nullable();
            $table->string('device_sub_type_model')->nullable();
            $table->string('device_sub_type_title')->nullable();
            $table->string('server_name')->nullable();
            $table->string('screen_model')->nullable();
            $table->string('projector_ip')->nullable();
            $table->string('sound_ip')->nullable();
            $table->string('number')->nullable();
            $table->string('projector_brand')->nullable();
            $table->string('projector_model')->nullable();
            $table->string('sound_brand')->nullable();
            $table->string('sound_model')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_tms_errors');
    }
};
