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
        Schema::table('iot_events', function (Blueprint $table) {
            $table->string('state')->nullable()->change();
            $table->json('attributes')->nullable()->after('state');
            $table->dropColumn(['brightness', 'brightness_percent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iot_events', function (Blueprint $table) {
            $table->string('state')->nullable(false)->change();
            $table->dropColumn('attributes');
            $table->tinyInteger('brightness')->nullable();
            $table->tinyInteger('brightness_percent')->nullable();
        });
    }
};
