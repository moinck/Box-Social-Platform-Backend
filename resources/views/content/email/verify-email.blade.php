<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
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
                    
                    <!-- Logo Section -->
                    <div style="text-align: center; margin-bottom: 24px;">
                        <img src="http://178.128.45.173:9162/assets/img/Box-media-logo.svg" 
                             alt="Box Social Logo" 
                             class="logo-img"
                             style="width: 100px; height: 100px; display: block; margin: 0 auto;">
                    </div>
                    
                    <!-- Title -->
                    <div class="email-title" style="text-align: center; font-size: 26px; color: #222; font-weight: 600; margin-bottom: 10px; line-height: 1.3;">
                        Almost There! ✨
                    </div>
                    
                    <!-- Description -->
                    <div class="email-description" style="text-align: center; font-size: 16px; color: #555; margin-bottom: 32px; line-height: 1.5;">
                        Just one more step — tap the button below to confirm your email and unlock your full experience.
                    </div>
                    
                    <!-- CTA Button -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <a href="{{ $verification_link }}" 
                           class="verify-button"
                           style="background-color: #F4D106; 
                                  color: #000; 
                                  padding: 14px 32px; 
                                  border-radius: 30px; 
                                  text-decoration: none; 
                                  font-weight: 600; 
                                  font-size: 16px; 
                                  display: inline-block; 
                                  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1); 
                                  transition: background-color 0.3s ease;
                                  border: none;
                                  cursor: pointer;">
                            Verify My Email
                        </a>
                    </div>
                    
                    <!-- Alternative Link (for clients that don't support buttons) -->
                    {{-- <div style="text-align: center; margin-bottom: 32px;">
                        <p style="font-size: 13px; color: #888; margin: 0;">
                            Or copy and paste this link in your browser:<br>
                            <a href="{{ $verification_link }}" style="color: #F4D106; word-break: break-all; font-size: 12px;">{{ $verification_link }}</a>
                        </p>
                    </div> --}}
                    
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