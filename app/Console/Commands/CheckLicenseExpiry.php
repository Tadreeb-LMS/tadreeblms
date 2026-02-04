<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use App\Mail\LicenseExpiryMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CheckLicenseExpiry extends Command
{
 protected $signature = 'license:expiry-check';
 protected $description = 'Send email if license expires within 30 days';

 public function handle()
 {
  $license = License::first();
  if(!$license || !$license->expires_at) return;

  $daysLeft = Carbon::now()
      ->diffInDays($license->expires_at, false);

  if($daysLeft <= 30 && $daysLeft >= 0)
  {
    $admins = AdminHelper::adminEmails();

    Mail::to($admins)
     ->send(new LicenseExpiryMail($license,$daysLeft));
  }
 }
}
