<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use App\Models\Auth\User;
use App\Helpers\AdminHelper;
use App\Mail\UserLimitExceededMail;
use Illuminate\Support\Facades\Mail;

class CheckUserLimit extends Command
{
 protected $signature = 'license:user-limit-check';
 protected $description = 'Send email if user limit reached or exceeded';

 public function handle()
 {
 $license = License::orderBy('id', 'desc')->first();
  if(!$license) return;

  $used = User::where('active', 1)->count();
  $max = $license->max_users;

  if($used >= $max)
  {
    Mail::to(AdminHelper::adminEmails())
      ->send(new UserLimitExceededMail($used,$max));
  }
 }
}
