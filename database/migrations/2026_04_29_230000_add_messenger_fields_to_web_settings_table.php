<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->boolean('messenger_enabled')->default(false)->after('instagram_url');
            $table->string('messenger_page_id')->nullable()->after('messenger_enabled');
            $table->string('messenger_theme_color', 20)->default('#B99D4B')->after('messenger_page_id');
            $table->string('messenger_logged_in_greeting')->nullable()->after('messenger_theme_color');
            $table->string('messenger_logged_out_greeting')->nullable()->after('messenger_logged_in_greeting');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn([
                'messenger_enabled',
                'messenger_page_id',
                'messenger_theme_color',
                'messenger_logged_in_greeting',
                'messenger_logged_out_greeting',
            ]);
        });
    }
};
