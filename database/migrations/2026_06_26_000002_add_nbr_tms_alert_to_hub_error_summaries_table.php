<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hub_error_summaries', function (Blueprint $table) {
            $table->integer('nbr_tms_alert')->default(0)->after('nbr_storage_errors');
        });
    }

    public function down(): void
    {
        Schema::table('hub_error_summaries', function (Blueprint $table) {
            $table->dropColumn('nbr_tms_alert');
        });
    }
};
