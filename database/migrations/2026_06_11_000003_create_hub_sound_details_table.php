<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hub_sound_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('screen_id')->constrained('hub_screens')->cascadeOnDelete();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('cat1700_serial_number')->nullable();
            $table->string('software')->nullable();
            $table->string('bypass')->nullable();
            $table->string('power_supply')->nullable();
            $table->string('aes_status')->nullable();
            $table->string('alert')->nullable();
            $table->string('screen_number')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['noc_instance_id', 'screen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_sound_details');
    }
};
