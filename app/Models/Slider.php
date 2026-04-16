<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image',
        'bg_color',
        'text_color',
        'button_text',
        'button_link',
        'sort_order',
        'is_active',
    ];
}
