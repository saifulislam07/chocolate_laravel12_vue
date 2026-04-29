<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->boolean('bkash_enabled')->default(false)->after('messenger_logged_out_greeting');
            $table->string('bkash_mode')->default('sandbox')->after('bkash_enabled');
            $table->string('bkash_base_url')->nullable()->after('bkash_mode');
            $table->string('bkash_app_key')->nullable()->after('bkash_base_url');
            $table->string('bkash_app_secret')->nullable()->after('bkash_app_key');
            $table->string('bkash_username')->nullable()->after('bkash_app_secret');
            $table->string('bkash_password')->nullable()->after('bkash_username');

            $table->boolean('nagad_enabled')->default(false)->after('bkash_password');
            $table->string('nagad_mode')->default('sandbox')->after('nagad_enabled');
            $table->string('nagad_base_url')->nullable()->after('nagad_mode');
            $table->string('nagad_merchant_id')->nullable()->after('nagad_base_url');
            $table->string('nagad_merchant_number')->nullable()->after('nagad_merchant_id');
            $table->longText('nagad_public_key')->nullable()->after('nagad_merchant_number');
            $table->longText('nagad_private_key')->nullable()->after('nagad_public_key');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn([
                'bkash_enabled',
                'bkash_mode',
                'bkash_base_url',
                'bkash_app_key',
                'bkash_app_secret',
                'bkash_username',
                'bkash_password',
                'nagad_enabled',
                'nagad_mode',
                'nagad_base_url',
                'nagad_merchant_id',
                'nagad_merchant_number',
                'nagad_public_key',
                'nagad_private_key',
            ]);
        });
    }
};
