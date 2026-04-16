<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSetting extends Model
{
    protected $fillable = [
        'site_name', 'logo', 'favicon', 'email', 'phone', 'address',
        'facebook_url', 'instagram_url', 'smtp_host', 'smtp_port',
        'smtp_username', 'smtp_password', 'smtp_encryption'
    ];
}
