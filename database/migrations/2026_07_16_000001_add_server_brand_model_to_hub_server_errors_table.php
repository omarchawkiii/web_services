<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_server_errors', function (Blueprint $table) {
            $table->string('server_brand')->nullable()->after('product_name');
            $table->string('server_model')->nullable()->after('server_brand');
        });
    }

    public function down(): void
    {
        Schema::table('hub_server_errors', function (Blueprint $table) {
            $table->dropColumn(['server_brand', 'server_model']);
        });
    }
};
