<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiExportNotification extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'progress',
        'format',
        'filters',
        'file_path',
        'download_link',
        'error_message',
        'processed_rows',
        'total_rows',
    ];

    protected $casts = [
        'filters' => 'array',
        'progress' => 'integer',
        'processed_rows' => 'integer',
        'total_rows' => 'integer',
    ];
}
