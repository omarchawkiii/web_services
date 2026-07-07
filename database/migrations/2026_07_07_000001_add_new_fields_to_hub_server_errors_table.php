<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_server_errors', function (Blueprint $table) {
            $table->string('ip_projector')->nullable()->after('recommended_action');
            $table->string('projector_brand')->nullable()->after('ip_projector');
            $table->string('projector_ip')->nullable()->after('projector_brand');
            $table->string('projector_model')->nullable()->after('projector_ip');
            $table->string('sound_brand')->nullable()->after('projector_model');
            $table->string('screen_model')->nullable()->after('sound_brand');
            $table->text('display_message')->nullable()->after('screen_model');
        });
    }

    public function down(): void
    {
        Schema::table('hub_server_errors', function (Blueprint $table) {
            $table->dropColumn(['ip_projector','projector_brand','projector_ip','projector_model','sound_brand','screen_model','display_message']);
        });
    }
};
