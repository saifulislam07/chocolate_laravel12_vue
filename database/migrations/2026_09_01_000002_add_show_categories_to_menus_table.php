<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a main menu item pull its sub menu from the product categories instead of
 * hand typed child items, so renaming a category never leaves a broken link.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->boolean('show_categories')->default(false)->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('show_categories');
        });
    }
};
