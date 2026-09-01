<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name', 'url', 'show_categories', 'parent_id', 'order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_categories' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }
}
