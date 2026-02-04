<?php

namespace App\Helpers;

use App\Models\Auth\User;
use Spatie\Permission\Models\Role;

class AdminHelper
{
  public static function adminEmails()
  {
     return User::whereHas('roles', function($q){
        $q->where('id',1);   // admin role_id
     })->pluck('email')->toArray();
  }
}
