<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class KpiDashboardSetting extends Model
{
    protected $fillable = [
        'scope',
        'widgets',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'widgets' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
