<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->string('projector_brand')->nullable()->after('recommended_action');
            $table->string('projector_model')->nullable()->after('projector_brand');
            $table->text('display_message')->nullable()->after('projector_model');
        });
    }

    public function down(): void
    {
        Schema::table('hub_projector_errors', function (Blueprint $table) {
            $table->dropColumn(['projector_brand','projector_model','display_message']);
        });
    }
};
