<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hub_kdm_errors', function (Blueprint $table) {
            $table->text('message')->nullable()->after('details');
            $table->text('recommended_action')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('hub_kdm_errors', function (Blueprint $table) {
            $table->dropColumn(['message', 'recommended_action']);
        });
    }
};
