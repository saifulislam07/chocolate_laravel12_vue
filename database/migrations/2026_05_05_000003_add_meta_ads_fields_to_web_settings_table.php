<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->boolean('meta_ads_enabled')->default(false)->after('meta_pixel_id');
            $table->string('meta_ads_api_version', 20)->default('v24.0')->after('meta_ads_enabled');
            $table->string('meta_ads_account_id', 80)->nullable()->after('meta_ads_api_version');
            $table->text('meta_ads_access_token')->nullable()->after('meta_ads_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn([
                'meta_ads_enabled',
                'meta_ads_api_version',
                'meta_ads_account_id',
                'meta_ads_access_token',
            ]);
        });
    }
};
