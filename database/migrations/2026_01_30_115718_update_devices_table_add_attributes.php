<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('current_state')->nullable()->change();
            $table->string('friendly_name')->nullable()->after('entity_id');
            $table->json('attributes')->nullable()->after('current_state');
            $table->dropColumn(['brightness', 'brightness_percent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('current_state')->nullable(false)->change();
            $table->dropColumn(['friendly_name', 'attributes']);
            $table->unsignedTinyInteger('brightness')->nullable();
            $table->unsignedTinyInteger('brightness_percent')->nullable();
        });
    }
};
