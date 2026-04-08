<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kpi extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'weight',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weight' => 'float',
    ];

    protected $attributes = [
        'weight' => 1,
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function statusHistories()
    {
        return $this->hasMany(KpiStatusHistory::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'kpi_course')->withTimestamps();
    }

    public function getTypeLabelAttribute()
    {
        return config('kpi.types.' . $this->type . '.label', ucfirst($this->type));
    }
}
