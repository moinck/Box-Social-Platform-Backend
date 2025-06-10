<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Box Social Email')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Email client support for media queries varies, but modern clients support them */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 20px auto !important;
                padding: 30px 20px !important;
                border-radius: 8px !important;
            }
            .email-title {
                font-size: 22px !important;
                margin-bottom: 12px !important;
            }
            .email-description {
                font-size: 15px !important;
                margin-bottom: 28px !important;
                padding: 0 10px !important;
            }
            .verify-button {
                padding: 12px 28px !important;
                font-size: 15px !important;
            }
            .logo-img {
                width: 80px !important;
                height: 80px !important;
            }
            .footer-text {
                font-size: 12px !important;
                margin-top: 32px !important;
                padding: 0 10px !important;
            }
        }
        
        @media only screen and (max-width: 480px) {
            .email-container {
                margin: 10px auto !important;
                padding: 25px 15px !important;
            }
            .email-title {
                font-size: 20px !important;
            }
            .email-description {
                font-size: 14px !important;
            }
            .verify-button {
                padding: 12px 24px !important;
                font-size: 14px !important;
            }
        }
    </style>
</head>
<body style="font-family: 'Inter', Arial, sans-serif; background-color: #f2f4f8; margin: 0; padding: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <!-- Centering wrapper table for better email client support -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f2f4f8; min-height: 100vh;">
        <tr>
            <td align="center" valign="middle" style="padding: 40px 20px;">
                <div class="email-container" style="max-width: 600px; width: 100%; background-color: #ffffff; padding: 40px 30px; border-radius: 12px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07); margin: 0 auto;">
                    
                    {{-- main content --}}
                    @yield('main-content')
                    
                    <!-- Footer -->
                    <div class="footer-text" style="text-align: center; font-size: 13px; color: #999; margin-top: 40px; line-height: 1.6;">
                        Didn't request this email?<br>
                        You can safely ignore it, and no changes will be made.<br><br>
                        <strong>— The Box Social Team</strong>
                    </div>
                    
                </div>
            </td>
        </tr>
    </table>
</body>
</html>