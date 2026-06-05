<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noc_instance_id')->constrained('noc_instances')->cascadeOnDelete();
            $table->unsignedBigInteger('noc_location_id'); // original ID in the NOC
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('company')->nullable();
            $table->string('tms_system')->nullable();
            $table->timestamps();

            $table->unique(['noc_instance_id', 'noc_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
