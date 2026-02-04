<!DOCTYPE html>
<html>
<body>

<h2>License Expiry Notice</h2>

<p>Dear Administrator,</p>

<p>
Your TadreebLMS license is about to expire.
</p>

<p>
<b>License Key:</b> {{ $license->license_key }} <br>
<b>Expiry Date:</b> {{ $license->expires_at }} <br>
<b>Days Remaining:</b> {{ $daysLeft }}
</p>

<p>
Please upgrade or renew your subscription to avoid service interruption.
</p>

<p>
Regards,<br>
TadreebLMS System
</p>

</body>
</html>
