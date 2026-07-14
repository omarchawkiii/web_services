<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_tms_errors', function (Blueprint $table) {
            $table->text('session_start')->nullable()->after('sound_model');
            $table->text('spl_title')->nullable()->after('session_start');
            $table->text('movie_title')->nullable()->after('spl_title');
        });
    }

    public function down(): void
    {
        Schema::table('hub_tms_errors', function (Blueprint $table) {
            $table->dropColumn(['session_start', 'spl_title', 'movie_title']);
        });
    }
};
