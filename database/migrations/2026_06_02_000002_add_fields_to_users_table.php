<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
            $table->string('username')->nullable()->unique()->after('last_name');
            $table->tinyInteger('role')->default(1)->after('username'); // 1=Admin 2=Manager 3=Cinema Staff
            $table->boolean('is_active')->default(true)->after('role');
            $table->foreignId('noc_instance_id')->nullable()->constrained('noc_instances')->nullOnDelete()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'username', 'role', 'is_active', 'noc_instance_id']);
        });
    }
};
