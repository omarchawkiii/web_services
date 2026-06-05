<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hub_projector_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->dateTime('time_saved')->nullable();
            $table->string('code')->nullable();
            $table->string('severity')->nullable();
            $table->text('message')->nullable();
            $table->string('server_name')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('hub_projector_errors'); }
};
