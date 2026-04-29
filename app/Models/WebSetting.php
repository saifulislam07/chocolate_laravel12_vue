<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSetting extends Model
{
    protected $fillable = [
        'site_name', 'logo', 'footer_logo', 'favicon', 'email', 'phone', 'address',
        'maintenance_mode', 'maintenance_title', 'maintenance_message',
        'facebook_url', 'instagram_url',
        'messenger_enabled', 'messenger_page_id', 'messenger_theme_color',
        'messenger_logged_in_greeting', 'messenger_logged_out_greeting',
        'bkash_enabled', 'bkash_mode', 'bkash_base_url', 'bkash_app_key',
        'bkash_app_secret', 'bkash_username', 'bkash_password',
        'nagad_enabled', 'nagad_mode', 'nagad_base_url', 'nagad_merchant_id',
        'nagad_merchant_number', 'nagad_public_key', 'nagad_private_key',
        'smtp_host', 'smtp_port',
        'smtp_username', 'smtp_password', 'smtp_encryption'
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
            'messenger_enabled' => 'boolean',
            'bkash_enabled' => 'boolean',
            'nagad_enabled' => 'boolean',
        ];
    }
}
