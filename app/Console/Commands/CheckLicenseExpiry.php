<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use App\Mail\LicenseExpiryMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Helpers\AdminHelper;

class CheckLicenseExpiry extends Command
{
 protected $signature = 'license:expiry-check';
 protected $description = 'Send email if license expires within 30 days';

 public function handle()
 {
  $license = License::orderBy('id', 'desc')->first();
  if(!$license || !$license->expiry_date) return;

  $daysLeft = Carbon::now()
      ->diffInDays($license->expiry_date, false);

  if($daysLeft <= 30 && $daysLeft >= 0)
  {
    $admins = AdminHelper::adminEmails();

    Mail::to($admins)
     ->send(new LicenseExpiryMail($license,$daysLeft));
  }
    // \Log::info("Expiry command executed");
 }
}
