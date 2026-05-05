<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->boolean('meta_pixel_enabled')->default(false)->after('instagram_url');
            $table->string('meta_pixel_id', 50)->nullable()->after('meta_pixel_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn(['meta_pixel_enabled', 'meta_pixel_id']);
        });
    }
};
