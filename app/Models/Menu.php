<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table;

    protected $fillable = ['name', 'status'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table =
            config('menu.table_prefix') .
            config('menu.table_name_menus');
    }

    /**
     * Get menu by name
     */
    public static function byName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Root menu items
     */
    public function items()
    {
        return $this->hasMany(MenuItems::class, 'menu')
            ->where('parent', 0)
            ->with('children')
            ->orderBy('sort', 'ASC');
    }
}
