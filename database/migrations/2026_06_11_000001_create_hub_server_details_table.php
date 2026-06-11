<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hub_server_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('screen_id')->constrained('hub_screens')->cascadeOnDelete();
            $table->string('screen_number')->nullable();
            $table->string('show_title')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('main_software_version')->nullable();
            $table->string('main_firmware_version')->nullable();
            $table->string('bundle_version')->nullable();
            $table->string('certificat_date')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['noc_instance_id', 'screen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_server_details');
    }
};
