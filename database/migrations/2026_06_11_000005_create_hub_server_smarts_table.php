<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hub_server_smarts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('screen_id')->constrained('hub_screens')->cascadeOnDelete();
            $table->string('overall_health')->nullable();
            $table->string('power_on_hours')->nullable();
            $table->string('raw_read_error')->nullable();
            $table->string('reallocated_event')->nullable();
            $table->string('reallocated_sector_count')->nullable();
            $table->string('smart_support')->nullable();
            $table->string('scan_date')->nullable();
            $table->string('seek_error_rate')->nullable();
            $table->string('temperature')->nullable();
            $table->string('udma_error')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_server_smarts');
    }
};
