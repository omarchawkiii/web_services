<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('noc_instances', function (Blueprint $table) {
            $table->string('api_key', 64)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('noc_instances', function (Blueprint $table) {
            $table->dropColumn('api_key');
        });
    }
};
