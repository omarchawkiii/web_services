<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hub_screens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->unsignedBigInteger('noc_screen_id');
            $table->string('screen_name');
            $table->integer('screen_number')->nullable();
            $table->string('screen_model')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['noc_instance_id', 'noc_screen_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('hub_screens'); }
};
