<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class CertificateHistory extends Model
{
    protected $fillable = [
        'certificate_id',
        'action',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
