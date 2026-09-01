<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * These fields are now edited with the rich text editor, so they store HTML
 * markup and no longer fit in a varchar(255).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->text('note')->nullable()->change();
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->text('reason')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('note')->nullable()->change();
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->string('reason')->nullable()->change();
        });
    }
};
