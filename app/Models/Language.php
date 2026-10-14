<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_enabled',
        'is_default',
    ];

    /**
     * Ensure only one default language
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($language) {
            if ($language->is_default) {
                self::where('id', '!=', $language->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get default language
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->first();
    }
}