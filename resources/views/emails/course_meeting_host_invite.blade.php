<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Host Invitation: {{ $course->title }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Arial', sans-serif; line-height: 1.6; color: #333333; background-color: #f0f2f5;">
    <div class="email-container" style="max-width: 600px; margin: 20px auto; background-color: #ffffff; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border-radius: 10px; overflow: hidden;">
        <!-- Header Section -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0"
            style="background: linear-gradient(180deg, #c09b4e, #233e74); text-align: center; color: white; padding-top: 30px; padding-bottom: 30px;">
            <tr>
                <td align="center">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td align="center">
                                <!-- spacing before logo -->
                                <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto; margin-bottom: 20px;">
                                    <tr> 
                                        <td style="text-align: center;" >
                                            <img src="{{ url('assets/img/logo.png') }}" style="background: linear-gradient(180deg, #ffffff, #ffffffa6); padding: 15px 20px 10px; border-radius: 10px;" alt="Tadreeb LMS" width="255" height="50">
                                        </td>
                                    </tr>
                                </table>

                                <!-- Heading -->
                                <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Meeting Host Invitation</h1>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Content Section -->
        <div style="padding: 30px 20px;">
            <p>Hello,</p>
            <p>You have been assigned as a teacher for the course: <strong>{{ $course->title }}</strong>, and a live meeting has been created.</p>
            
            <table width="100%" style="background-color: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 10px; border: 1px solid #eaeaea;">
                <tr>
                    <td>
                        <p style="margin-top: 0;"><strong>Date & Time:</strong> {{ \Carbon\Carbon::parse($course->meeting_start_at)->format('F j, Y g:i A') }} ({{ $course->meeting_timezone }})</p>
                        <p style="margin-bottom: 0;"><strong>Duration:</strong> {{ $course->meeting_duration }} minutes</p>
                        
                        @if($course->meeting_host_url)
                            <div style="margin-top: 25px; text-align: center;">
                                <a href="{{ $course->meeting_host_url }}" style="display: inline-block; padding: 12px 30px; background: linear-gradient(90deg, #223a6a, #cc8a03); color: white; text-decoration: none; border-radius: 30px; font-size: 16px;">Start the Meeting as Host</a>
                            </div>
                            <p style="font-size: 0.9em; word-break: break-all; margin-top: 15px;">
                                Or copy and paste this link into your browser to start the meeting:<br>
                                <a href="{{ $course->meeting_host_url }}" style="color: #223a6a;">{{ $course->meeting_host_url }}</a>
                            </p>
                        @endif

                        @if($course->meeting_join_url)
                            <div style="margin-top: 20px;">
                                <p style="font-size: 0.9em; margin-bottom: 5px;"><strong>Participant Join URL:</strong></p>
                                <a href="{{ $course->meeting_join_url }}" style="font-size: 0.9em; word-break: break-all; color: #223a6a;">{{ $course->meeting_join_url }}</a>
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
            
            <p>Best regards,<br>Tadreeb LMS Team</p>
        </div>
        
        <!-- Footer Section -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9f9f9; border-top: 1px solid #eaeaea;">
            <tr>
                <td align="center" style="padding: 20px;">
                    <table cellpadding="0" cellspacing="0" border="0" align="center">
                        <tr>
                            <td style="padding: 10px;">
                                <a href="#" target="_blank">
                                    <img src="https://cdn-icons-png.flaticon.com/256/124/124010.png" alt="Facebook" width="30" height="30" style="display: block; border: 0;">
                                </a>
                            </td>
                            <td style="padding: 10px;">
                                <a href="#" target="_blank">
                                    <img src="https://w7.pngwing.com/pngs/748/680/png-transparent-twitter-x-logo.png" alt="Twitter" width="30" height="30" style="display: block; border: 0;">
                                </a>
                            </td>
                            <td style="padding: 10px;">
                                <a href="#" target="_blank">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/09/YouTube_full-color_icon_%282017%29.svg/1280px-YouTube_full-color_icon_%282017%29.svg.png" alt="YouTube" width="30" height="30" style="display: block; border: 0;">
                                </a>
                            </td>
                            <td style="padding: 10px;">
                                <a href="https://www.linkedin.com/company/tadreeblms/" target="_blank">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/81/LinkedIn_icon.svg/2048px-LinkedIn_icon.svg.png" alt="LinkedIn" width="30" height="30" style="display: block; border: 0;">
                                </a>
                            </td>
                            <td style="padding: 10px;">
                                <a href="https://www.instagram.com/tadreeblms?igsh=b3gzOG83NGE5cncx&amp;utm_source=qr" target="_blank">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Instagram_icon.png/1024px-Instagram_icon.png" alt="Instagram" width="30" height="30" style="display: block; border: 0;">
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
