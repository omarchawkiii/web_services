<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hub_light_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('screen_id')->constrained('hub_screens')->cascadeOnDelete();
            $table->string('hours')->nullable();
            $table->integer('index_lamp')->nullable();
            $table->string('power_range')->nullable();
            $table->string('rotation_state')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('type')->nullable();
            $table->string('date_lamp')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_light_histories');
    }
};
