<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'use_case',
        'item_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'item_count' => 'int',
    ];

    /**
     * Get all KPI items in this template.
     */
    public function items()
    {
        return $this->hasMany(KpiTemplateItem::class, 'template_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Get active items only.
     */
    public function activeItems()
    {
        return $this->items()->where('is_active', true);
    }

    /**
     * Generate a slug from the name.
     */
    public static function generateSlug($name)
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug = static::generateSlug($model->name);
            }
        });

        static::saving(function ($model) {
            $model->item_count = $model->items()->count();
        });
    }
}
