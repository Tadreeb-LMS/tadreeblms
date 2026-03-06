<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Invitation: {{ $course->title }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h2 style="color: #0056b3;">Meeting Invitation</h2>
        <p>Hello,</p>
        <p>You have been invited to a live meeting for the course: <strong>{{ $course->title }}</strong>.</p>
        
        <div style="background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <p><strong>Date & Time:</strong> {{ \Carbon\Carbon::parse($course->meeting_start_at)->format('F j, Y g:i A') }} ({{ $course->meeting_timezone }})</p>
            <p><strong>Duration:</strong> {{ $course->meeting_duration }} minutes</p>
            
            @if($course->meeting_join_url)
                <p style="margin-top: 20px;">
                    <a href="{{ $course->meeting_join_url }}" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Join the Meeting</a>
                </p>
                <p style="font-size: 0.9em; word-break: break-all;">
                    Or copy and paste this link into your browser:<br>
                    <a href="{{ $course->meeting_join_url }}">{{ $course->meeting_join_url }}</a>
                </p>
            @endif
        </div>
        
        <p>We look forward to seeing you there!</p>
        <p>Best regards,<br>Tadreeb LMS Team</p>
    </div>
</body>
</html>
