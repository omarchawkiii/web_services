<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->string('id_projector_errors')->nullable()->after('ip_projector');
        });
    }

    public function down(): void
    {
        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->dropColumn('id_projector_errors');
        });
    }
};
