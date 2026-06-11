<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hub_storage_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('screen_id')->constrained('hub_screens')->cascadeOnDelete();
            $table->string('bus')->nullable();
            $table->string('capacity')->nullable();
            $table->string('index_storage')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('working_state')->nullable();
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->string('version')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_storage_devices');
    }
};
