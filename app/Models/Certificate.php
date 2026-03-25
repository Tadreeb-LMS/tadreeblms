<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'course_id',
        'certificate_id',
        'validation_hash',
        'url',
        'file_path',
        'status',
        'revoked_at',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'json',
        'revoked_at' => 'datetime',
    ];

    protected $appends = ['certificate_link'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function getCertificateLinkAttribute(){
        if ($this->url != null) {
            return url('storage/certificates/'.$this->url);
        }
        return NULL;
    }

    /**
     * Check if the certificate has been revoked.
     *
     * @return bool
     */
    public function isRevoked()
    {
        return !is_null($this->revoked_at);
    }
}
