<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noc_instances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('admin_username');
            $table->text('admin_password');
            $table->text('sanctum_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('sync_status')->default('unknown'); // online | offline | syncing | unknown
            $table->timestamp('last_sync_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noc_instances');
    }
};
