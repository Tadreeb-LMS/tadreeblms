<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTemplateItem extends Model
{
    protected $fillable = [
        'template_id',
        'name',
        'code',
        'description',
        'type',
        'weight',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'weight' => 'float',
        'sort_order' => 'int',
    ];

    /**
     * Get the parent template.
     */
    public function template()
    {
        return $this->belongsTo(KpiTemplate::class, 'template_id');
    }

    /**
     * Get total weight of all items.
     */
    public static function totalWeight($templateId)
    {
        return static::where('template_id', $templateId)
            ->where('is_active', true)
            ->sum('weight');
    }
}
