<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Support\Facades\Schema;
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

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'kpi_category')->withTimestamps();
    }

    public function snapshots()
    {
        return $this->hasMany(KpiSnapshot::class);
    }

    public function currentSnapshot()
    {
        return $this->hasOne(KpiSnapshot::class)->where('is_current', true)->latest('id');
    }

    public function getTypeLabelAttribute()
    {
        return config('kpi.types.' . $this->type . '.label', ucfirst($this->type));
    }

    /**
     * Resolve all course IDs that should be included for this KPI.
     * Priority: category-mapped + include_in_kpi=true, then legacy explicit course mapping.
     *
     * @return int[]
     */
    public function resolveScopedCourseIds(): array
    {
        $categoryIds = $this->relationLoaded('categories')
            ? $this->categories->pluck('id')->toArray()
            : $this->categories()->pluck('categories.id')->toArray();

        $categoryIds = collect($categoryIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (!empty($categoryIds) && Schema::hasTable('courses')) {
            $query = Course::query()->whereIn('category_id', $categoryIds);

            if (Schema::hasColumn('courses', 'include_in_kpi')) {
                $query->where('include_in_kpi', true);
            }

            if (Schema::hasColumn('courses', 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            return $query->pluck('id')->map(function ($id) {
                return (int) $id;
            })->values()->toArray();
        }

        $legacyCourseIds = $this->relationLoaded('courses')
            ? $this->courses->pluck('id')->toArray()
            : $this->courses()->pluck('courses.id')->toArray();

        return collect($legacyCourseIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
