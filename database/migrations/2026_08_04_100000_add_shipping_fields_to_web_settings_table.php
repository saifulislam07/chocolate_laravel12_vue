<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            // Fallback charge used when an area has no rate of its own.
            $table->decimal('default_shipping_charge', 10, 2)->default(120)->after('address');
            // Null disables free shipping entirely.
            $table->decimal('free_shipping_threshold', 10, 2)->nullable()->after('default_shipping_charge');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn(['default_shipping_charge', 'free_shipping_threshold']);
        });
    }
};
