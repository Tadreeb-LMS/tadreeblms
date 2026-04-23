<?php

namespace App\Models;

use App\Models\Auth\Role;
use Illuminate\Database\Eloquent\Model;

class KpiTarget extends Model
{
    protected $fillable = [
        'kpi_id',
        'role_id',
        'course_id',
        'target_value',
    ];

    protected $casts = [
        'target_value' => 'float',
    ];

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
