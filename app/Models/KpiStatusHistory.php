<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class KpiStatusHistory extends Model
{
    protected $fillable = [
        'kpi_id',
        'action',
        'previous_is_active',
        'new_is_active',
        'changed_by',
        'meta',
    ];

    protected $casts = [
        'previous_is_active' => 'boolean',
        'new_is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
