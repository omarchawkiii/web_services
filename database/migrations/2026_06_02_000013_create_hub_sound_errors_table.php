<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hub_sound_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('alarm_id')->nullable();
            $table->bigInteger('date_saved')->nullable();
            $table->string('severity')->nullable();
            $table->string('title')->nullable();
            $table->tinyInteger('clearable')->nullable();
            $table->string('hardware')->nullable();
            $table->string('screen')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('hub_sound_errors'); }
};
