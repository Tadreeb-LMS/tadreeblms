<?php

namespace App\Models;

use App\Helpers\AppResolver;
use App\Models\OauthClient;
use App\Scopes\AppScope;
use Illuminate\Database\Eloquent\Model;

class AppClient extends Model
{

    public function client(){
        return $this->belongsTo(OauthClient::class,'client_id');
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AppScope( new AppResolver()));
    }
}
