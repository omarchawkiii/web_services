<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hub_server_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('event_id')->nullable();
            $table->dateTime('date')->nullable();
            $table->string('class')->nullable();
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('criticity')->nullable();
            $table->string('error_code')->nullable();
            $table->string('server_name')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('hub_server_errors'); }
};
