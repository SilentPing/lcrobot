<?php
/**
 * Email Templates for Civil Registry System
 * 
 * This file contains all email templates used in the system
 */

/**
 * Generate Password Reset Email Template
 * 
 * @param string $user_name User's name
 * @param string $reset_link Password reset link
 * @param string $expiry_time When the link expires
 * @return string HTML email template
 */
function getPasswordResetTemplate($user_name, $reset_link, $expiry_time) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset - Botolan Civil Registry</title>
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f4f4f4;
            }
            .email-container {
                background-color: #ffffff;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #c41e67 0%, #a71555 100%);
                color: white;
                padding: 30px 20px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 600;
            }
            .header p {
                margin: 10px 0 0 0;
                opacity: 0.9;
                font-size: 14px;
            }
            .content {
                padding: 30px 20px;
            }
            .greeting {
                font-size: 18px;
                margin-bottom: 20px;
                color: #2c3e50;
            }
            .message {
                font-size: 16px;
                margin-bottom: 25px;
                color: #555;
            }
            .reset-button {
                display: inline-block;
                background: linear-gradient(135deg, #c41e67 0%, #a71555 100%);
                color: white;
                padding: 15px 30px;
                text-decoration: none;
                border-radius: 25px;
                font-weight: 600;
                font-size: 16px;
                margin: 20px 0;
                transition: transform 0.2s ease;
            }
            .reset-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(196, 30, 103, 0.3);
            }
            .security-info {
                background-color: #f8f9fa;
                border-left: 4px solid #c41e67;
                padding: 15px;
                margin: 20px 0;
                border-radius: 0 5px 5px 0;
            }
            .security-info h3 {
                margin: 0 0 10px 0;
                color: #c41e67;
                font-size: 16px;
            }
            .security-info ul {
                margin: 0;
                padding-left: 20px;
            }
            .security-info li {
                margin-bottom: 5px;
                font-size: 14px;
                color: #666;
            }
            .footer {
                background-color: #f8f9fa;
                padding: 20px;
                text-align: center;
                border-top: 1px solid #e9ecef;
            }
            .footer p {
                margin: 5px 0;
                font-size: 14px;
                color: #666;
            }
            .footer a {
                color: #c41e67;
                text-decoration: none;
            }
            .footer a:hover {
                text-decoration: underline;
            }
            .logo {
                max-width: 80px;
                height: auto;
                margin-bottom: 10px;
            }
            @media (max-width: 600px) {
                body {
                    padding: 10px;
                }
                .header, .content, .footer {
                    padding: 20px 15px;
                }
                .reset-button {
                    display: block;
                    text-align: center;
                    margin: 20px auto;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <img src="' . SITE_URL . '/images/civ.png" alt="MCRO Logo" class="logo">
                <h1>Password Reset Request</h1>
                <p>Botolan Civil Registry Online Portal</p>
            </div>
            
            <div class="content">
                <div class="greeting">
                    Hello ' . htmlspecialchars($user_name) . ',
                </div>
                
                <div class="message">
                    We received a request to reset your password for your Botolan Civil Registry Online Portal account. 
                    If you made this request, click the button below to reset your password.
                </div>
                
                <div style="text-align: center;">
                    <a href="' . $reset_link . '" class="reset-button">Reset My Password</a>
                </div>
                
                <div class="security-info">
                    <h3>🔒 Security Information</h3>
                    <ul>
                        <li>This link will expire in <strong>' . $expiry_time . '</strong></li>
                        <li>If you didn\'t request this reset, please ignore this email</li>
                        <li>Your password will remain unchanged until you create a new one</li>
                        <li>For security, this link can only be used once</li>
                    </ul>
                </div>
                
                <div class="message">
                    If the button above doesn\'t work, you can copy and paste this link into your browser:
                    <br><br>
                    <a href="' . $reset_link . '" style="color: #c41e67; word-break: break-all;">' . $reset_link . '</a>
                </div>
            </div>
            
            <div class="footer">
                <p><strong>Botolan Civil Registry Office</strong></p>
                <p>Municipality of Botolan, Zambales</p>
                <p>📧 Email: <a href="mailto:info@lcro.pcbics.net">info@lcro.pcbics.net</a></p>
                <p>🌐 Website: <a href="' . SITE_URL . '">' . SITE_URL . '</a></p>
                <p style="margin-top: 15px; font-size: 12px; color: #999;">
                    This is an automated message. Please do not reply to this email.
                </p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Generate Password Reset Success Email Template
 * 
 * @param string $user_name User's name
 * @return string HTML email template
 */
function getPasswordResetSuccessTemplate($user_name) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset Successful - Botolan Civil Registry</title>
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f4f4f4;
            }
            .email-container {
                background-color: #ffffff;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                padding: 30px 20px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 600;
            }
            .content {
                padding: 30px 20px;
                text-align: center;
            }
            .success-icon {
                font-size: 48px;
                color: #28a745;
                margin-bottom: 20px;
            }
            .greeting {
                font-size: 18px;
                margin-bottom: 20px;
                color: #2c3e50;
            }
            .message {
                font-size: 16px;
                margin-bottom: 25px;
                color: #555;
            }
            .login-button {
                display: inline-block;
                background: linear-gradient(135deg, #c41e67 0%, #a71555 100%);
                color: white;
                padding: 15px 30px;
                text-decoration: none;
                border-radius: 25px;
                font-weight: 600;
                font-size: 16px;
                margin: 20px 0;
            }
            .footer {
                background-color: #f8f9fa;
                padding: 20px;
                text-align: center;
                border-top: 1px solid #e9ecef;
            }
            .footer p {
                margin: 5px 0;
                font-size: 14px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <h1>✅ Password Reset Successful</h1>
                <p>Botolan Civil Registry Online Portal</p>
            </div>
            
            <div class="content">
                <div class="success-icon">🔐</div>
                
                <div class="greeting">
                    Hello ' . htmlspecialchars($user_name) . ',
                </div>
                
                <div class="message">
                    Your password has been successfully reset! You can now log in to your account using your new password.
                </div>
                
                <div>
                    <a href="' . SITE_URL . '/login.php" class="login-button">Login to Your Account</a>
                </div>
                
                <div class="message">
                    If you didn\'t make this change, please contact our support team immediately.
                </div>
            </div>
            
            <div class="footer">
                <p><strong>Botolan Civil Registry Office</strong></p>
                <p>Municipality of Botolan, Zambales</p>
                <p>📧 Email: <a href="mailto:info@lcro.pcbics.net">info@lcro.pcbics.net</a></p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Generate plain text version of password reset email
 * 
 * @param string $user_name User's name
 * @param string $reset_link Password reset link
 * @param string $expiry_time When the link expires
 * @return string Plain text email
 */
function getPasswordResetTextTemplate($user_name, $reset_link, $expiry_time) {
    return "
Password Reset Request - Botolan Civil Registry Online Portal

Hello " . $user_name . ",

We received a request to reset your password for your Botolan Civil Registry Online Portal account.

To reset your password, please click the following link:
" . $reset_link . "

This link will expire in " . $expiry_time . ".

If you didn't request this password reset, please ignore this email. Your password will remain unchanged.

Security Information:
- This link can only be used once
- The link expires in " . $expiry_time . "
- If you didn't request this, please contact support

Best regards,
Botolan Civil Registry Office
Municipality of Botolan, Zambales

Email: info@lcro.pcbics.net
Website: " . SITE_URL . "

This is an automated message. Please do not reply to this email.
";
}
?>
