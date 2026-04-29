<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->boolean('maintenance_mode')->default(false)->after('address');
            $table->string('maintenance_title')->nullable()->after('maintenance_mode');
            $table->text('maintenance_message')->nullable()->after('maintenance_title');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn(['maintenance_mode', 'maintenance_title', 'maintenance_message']);
        });
    }
};
