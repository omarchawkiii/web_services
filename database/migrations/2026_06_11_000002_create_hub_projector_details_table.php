<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hub_projector_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('screen_id')->constrained('hub_screens')->cascadeOnDelete();
            $table->string('system_model')->nullable();
            $table->string('system_serial_number')->nullable();
            $table->string('system_manufacture_date')->nullable();
            $table->string('system_software_version')->nullable();
            $table->string('operating_hours')->nullable();
            $table->string('power_status')->nullable();
            $table->string('shutter_status')->nullable();
            $table->string('light_status')->nullable();
            $table->string('light_model')->nullable();
            $table->string('light_serial')->nullable();
            $table->string('light_hours')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['noc_instance_id', 'screen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_projector_details');
    }
};
