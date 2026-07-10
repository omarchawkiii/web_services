<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_server_errors', function (Blueprint $table) {
            $table->string('certificat_date')->nullable()->after('display_message');
            $table->string('serial_number')->nullable()->after('certificat_date');
            $table->string('show_title')->nullable()->after('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('hub_server_errors', function (Blueprint $table) {
            $table->dropColumn(['certificat_date', 'serial_number', 'show_title']);
        });
    }
};
