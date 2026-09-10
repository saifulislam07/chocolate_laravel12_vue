<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            // Pathao sends this back as the X-PATHAO-Signature header on every
            // webhook call; it is the only thing proving the call came from them.
            $table->string('pathao_webhook_secret')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn('pathao_webhook_secret');
        });
    }
};
