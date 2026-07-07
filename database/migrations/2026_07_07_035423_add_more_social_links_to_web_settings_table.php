<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('instagram_url');
            $table->string('whatsapp_url')->nullable()->after('youtube_url');
            $table->string('tiktok_url')->nullable()->after('whatsapp_url');
            $table->string('linkedin_url')->nullable()->after('tiktok_url');
            $table->string('pinterest_url')->nullable()->after('linkedin_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn(['youtube_url', 'whatsapp_url', 'tiktok_url', 'linkedin_url', 'pinterest_url']);
        });
    }
};
