<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hub_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('screen_id')->nullable()->constrained('hub_screens')->nullOnDelete();
            $table->string('noc_schedule_id');
            $table->string('display_title')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('date_start');
            $table->dateTime('date_end')->nullable();
            $table->string('status')->default('unlinked');
            $table->tinyInteger('cpls')->default(0);
            $table->tinyInteger('kdm')->default(0);
            $table->json('kdm_notes')->nullable();
            $table->text('list_cpl_notes')->nullable();
            $table->string('uuid_spl')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['noc_instance_id', 'noc_schedule_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('hub_schedules'); }
};
