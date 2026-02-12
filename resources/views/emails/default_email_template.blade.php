<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template with Tables</title>
    <style>
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .social-icons a:hover {
            transform: scale(1.2);
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .content-padding {
            padding: 30px 20px !important;
        }

        .main-heading {
            font-size: 28px;
        }

        .sub-heading {
            font-size: 20px;
        }

        .log-container-1 {
            height: 100px;
            width: 100px;
            background-color: white;
            /* background-image: url("https://updated-academy.delta-medlab.com/img/white-bg.jpg");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center; */
            border-radius: 50%;
            text-align: center;
            color-scheme: light !important;
        }

        .logo-1 {
            height: 100px;
            width: 100px;
            border-radius: 50%;
        }

        .logo-2 {
            height: 100px;
            width: 100px;
            border-radius: 50%;
        }

        .page-content {
            padding: 20px 20px;
        }

        @media screen and (max-width: 800px) {
            .page-content {
                padding: 20px 10px;
            }

            .main-heading {
                font-size: 20px;
            }

            .sub-heading {
                font-size: 14px;
            }

            .log-container-1 {
                height: 80px;
                width: 80px;
                background-color: white;
                /* background-image: url("https://updated-academy.delta-medlab.com/img/white-bg.jpg");
                background-repeat: no-repeat;
                background-size: cover;
                background-position: center; */
                border-radius: 50%;
                text-align: center;
                color-scheme: light !important;
            }

            .logo-1 {
                height: 100px;
                width: 100px;
                border-radius: 50%;
            }

            .logo-2 {
                height: 100px;
                width: 100px;
                border-radius: 50%;
            }
        }

        @media screen and (max-width: 480px) {
            .feature-box {
                font-size: 11px;
            }

            .page-content {
                padding: 10px 10px;
            }

            .main-heading {
                font-size: 16px !important;
            }

            .sub-heading {
                font-size: 12px !important;
            }

            .log-container-1 {
                height: 60px;
                width: 60px;
                background-color: white;
                /* background-image: url("https://updated-academy.delta-medlab.com/img/white-bg.jpg");
                background-repeat: no-repeat;
                background-size: cover;
                background-position: center; */
                border-radius: 50%;
                text-align: center;
                color-scheme: light !important;
            }

            .logo-1 {
                height: 100px;
                width: 100px;
                border-radius: 50%;
            }

            .logo-2 {
                height: 100px;
                width: 100px;
                border-radius: 50%;
            }

            .logo-container {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }

            .logo-cell {
                width: 100px;
                height: 100px;
            }

            .cta-button {
                display: block;
                text-align: center;
                margin: 25px auto;
            }
        }

        @media (prefers-color-scheme: dark) {
            .log-container-1 {
                /* background-color: #ffffff !important; */

            }
        }


        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animated {
            animation: fadeIn 0.8s ease-out;
        }
    </style>
</head>

<body style="margin: 0; padding: 0; font-family: 'Arial', sans-serif; line-height: 1.6; color: #333333; background-color: #f0f2f5;">
    <div class="email-container" style="max-width: 600px; margin: 20px auto; background-color: #ffffff; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border-radius: 10px; overflow: hidden;">
        <!-- Header Section -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0"
            style="background: linear-gradient(180deg, #c09b4e, #233e74); text-align: center; color: white;padding-top: 30px;">
            <tr>
                <td align="center">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td align="center">
                                <!-- spacing before logo -->
                                <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                                    <tr> 
                                        <td style="text-align: center;" >
                                            <img src="http://test.tadreeblms.com/assets/img/logo.png" style="    background: linear-gradient(180deg, #ffffff, #ffffffa6);
    padding: 15px 20px 10px;
    border-radius: 10px;" alt="Tadreeb LMS" width="255" height="50">
                                        </td>
                                    </tr>
                                </table>

                                <!-- Heading -->
                                <h1 style="color: #ffffff;" class="main-heading">{{ $content['email_heading'] }}</h1>
                                <p style="color: #ffffff;" class="sub-heading">{{ $content['sub_heading'] }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>



        <!-- Content Section -->

        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="content-section" style="background-color: #ffffff;">
            <tr>
                <td class="content-padding" style="padding: 30px 20px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>
                                <table class="feature-box" width="100%" style="background: #f9f9f9; border-radius: 10px; padding: 30px; border: 1px solid #eaeaea;">
                                    <tr>
                                        <td>
                                            {!! $content['email_content'] !!}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        @if(isset($content['register_button']))
                        <tr>
                            <td>
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td align="center">
                                            <div style="padding-top:20px;">
                                            <a href="{{ $content['register_button'] }}"
                                            style="color:#ffffff;
                                                    padding:10px 30px;
                                                    text-decoration:none;
                                                    font-size:16px;
                                                    background: linear-gradient(90deg, #223a6a, #cc8a03); border-radius: 30px;
                                                    display:inline-block;">
                                                Register Now
                                            </a>
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
        <!-- Content Section -->


        <!-- Footer Section -->

        <table cellpadding="0" cellspacing="0" border="0" align="center">
            <tr>
                <td style="padding: 10px;">
                    <a href="#" target="_blank">
                        <img src="https://cdn-icons-png.flaticon.com/256/124/124010.png" alt="Facebook" width="36" height="36" style="display: block; border: 0;">
                    </a>
                </td>
                <td style="padding: 10px;">
                    <a href="#" target="_blank">
                        <img src="https://w7.pngwing.com/pngs/748/680/png-transparent-twitter-x-logo.png" alt="Twitter" width="36" height="36" style="display: block; border: 0;">
                    </a>
                </td>
                <td style="padding: 10px;">
                    <a href="#" target="_blank">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/09/YouTube_full-color_icon_%282017%29.svg/1280px-YouTube_full-color_icon_%282017%29.svg.png" alt="YouTube" width="36" height="36" style="display: block; border: 0;">
                    </a>
                </td>
                <td style="padding: 10px;">
                    <a href="https://www.linkedin.com/company/tadreeblms/" target="_blank">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/81/LinkedIn_icon.svg/2048px-LinkedIn_icon.svg.png" alt="LinkedIn" width="36" height="36" style="display: block; border: 0;">
                    </a>
                </td>
                <td style="padding: 10px;">
                    <a href="https://www.instagram.com/tadreeblms?igsh=b3gzOG83NGE5cncx&amp;utm_source=qr" target="_blank">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Instagram_icon.png/1024px-Instagram_icon.png" alt="Instagram" width="36" height="36" style="display: block; border: 0;">
                    </a>
                </td>
            </tr>
        </table>
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td height="20" style="font-size: 0; line-height: 0;">&nbsp;</td>
            </tr>
        </table>

    </div>
</body>

</html>