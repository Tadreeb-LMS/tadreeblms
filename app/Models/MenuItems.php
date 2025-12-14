<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItems extends Model
{
    protected $fillable = [
        'label',
        'link',
        'parent',
        'sort',
        'class',
        'menu',
        'depth',
        'role_id',
    ];

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table =
            config('menu.table_prefix') .
            config('menu.table_name_items');
    }

    // Children of a menu item
    public function children()
    {
        return $this->hasMany(self::class, 'parent')
            ->orderBy('sort', 'ASC');
    }

    // Parent menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu');
    }

    // Helpers
    public function getSons($id)
    {
        return static::where('parent', $id)->get();
    }

    public function getAllByMenu($menuId)
    {
        return static::where('menu', $menuId)
            ->orderBy('sort', 'ASC')
            ->get();
    }

    public static function getNextSortRoot($menuId)
    {
        return (int) static::where('menu', $menuId)->max('sort') + 1;
    }
}
