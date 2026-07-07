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
            $table->boolean('pathao_enabled')->default(false);
            $table->string('pathao_base_url')->nullable();
            $table->string('pathao_client_id')->nullable();
            $table->string('pathao_client_secret')->nullable();
            $table->string('pathao_username')->nullable();
            $table->string('pathao_password')->nullable();
            $table->string('pathao_store_id')->nullable();

            $table->boolean('steadfast_enabled')->default(false);
            $table->string('steadfast_base_url')->nullable();
            $table->string('steadfast_api_key')->nullable();
            $table->string('steadfast_secret_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn([
                'pathao_enabled', 'pathao_base_url', 'pathao_client_id', 'pathao_client_secret',
                'pathao_username', 'pathao_password', 'pathao_store_id',
                'steadfast_enabled', 'steadfast_base_url', 'steadfast_api_key', 'steadfast_secret_key',
            ]);
        });
    }
};
