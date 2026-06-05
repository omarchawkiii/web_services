<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hub_storage_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('server_name')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('hub_storage_errors'); }
};
