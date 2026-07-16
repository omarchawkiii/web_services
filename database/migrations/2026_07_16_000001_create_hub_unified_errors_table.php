<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hub_unified_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('device_type');
            $table->string('external_key')->nullable();
            $table->text('message')->nullable();
            $table->string('screen_name')->nullable();
            $table->string('screen_number')->nullable();
            $table->string('device_ip')->nullable();
            $table->text('display_message')->nullable();
            $table->text('recommended_action')->nullable();
            $table->string('severity')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('date_error')->nullable();
            $table->string('device_sub_type')->nullable();
            $table->string('device_sub_type_ip')->nullable();
            $table->string('device_sub_type_model')->nullable();
            $table->string('device_sub_type_title')->nullable();
            $table->text('movie_title')->nullable();
            $table->text('spl_title')->nullable();
            $table->string('session_start')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['noc_instance_id', 'location_id', 'device_type', 'external_key'], 'hub_unified_errors_unique_key');
            $table->index(['noc_instance_id', 'location_id', 'device_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_unified_errors');
    }
};
