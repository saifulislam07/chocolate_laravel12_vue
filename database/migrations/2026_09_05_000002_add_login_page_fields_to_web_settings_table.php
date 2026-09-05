<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The two login screens — the shopper's and the admin's — were built from
     * hardcoded artwork and copy. These let the shop change what a visitor
     * reads on the way in without a deploy.
     *
     * Every column is nullable and stays that way: an empty setting means the
     * page keeps the wording it shipped with, so nothing has to be filled in
     * before the screens work.
     */
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table): void {
            $table->string('login_image')->nullable()->after('favicon');
            $table->string('login_cover_title')->nullable()->after('login_image');
            $table->string('login_cover_text')->nullable()->after('login_cover_title');
            $table->string('login_form_title')->nullable()->after('login_cover_text');
            $table->text('login_form_text')->nullable()->after('login_form_title');

            $table->string('admin_login_image')->nullable()->after('login_form_text');
            $table->string('admin_login_cover_title')->nullable()->after('admin_login_image');
            $table->string('admin_login_cover_text')->nullable()->after('admin_login_cover_title');
            $table->string('admin_login_form_title')->nullable()->after('admin_login_cover_text');
            $table->text('admin_login_form_text')->nullable()->after('admin_login_form_title');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'login_image',
                'login_cover_title',
                'login_cover_text',
                'login_form_title',
                'login_form_text',
                'admin_login_image',
                'admin_login_cover_title',
                'admin_login_cover_text',
                'admin_login_form_title',
                'admin_login_form_text',
            ]);
        });
    }
};
