<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/auth_phpmailer_Exception.php';
require_once __DIR__ . '/auth_phpmailer_PHPMailer.php';
require_once __DIR__ . '/auth_phpmailer_SMTP.php';
require_once __DIR__ . '/auth_mail_config.php';

/**
 * Send OTP email via Gmail SMTP.
 *
 * @param string $to_email   Recipient email address
 * @param string $to_name    Recipient display name
 * @param string $otp        The 6-digit OTP code
 * @return true|string       Returns true on success, error message on failure
 */
function sendOtpEmail($to_email, $to_name, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo(SMTP_FROM, SMTP_FROM_NAME);

        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset OTP — Student Assist';
        $mail->Body    = "
        <div style='font-family:Inter,Arial,sans-serif;max-width:480px;margin:0 auto;background:#f4f6fb;padding:30px;border-radius:16px;'>
            <div style='background:#2563eb;border-radius:12px;padding:24px;text-align:center;margin-bottom:24px;'>
                <h1 style='color:#fff;margin:0;font-size:22px;'>&#x1F4DA; Student Assist</h1>
                <p style='color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:13px;'>Your Study Partner</p>
            </div>
            <h2 style='color:#1a3fa0;font-size:18px;margin-bottom:8px;'>Password Reset Request</h2>
            <p style='color:#444;font-size:14px;line-height:1.6;'>
                Hi <strong>$to_name</strong>,<br><br>
                We received a request to reset the password for your Student Assist account.
                Use the OTP code below to proceed. This code is valid for <strong>10 minutes</strong>.
            </p>
            <div style='background:#fff;border:2px dashed #2563eb;border-radius:12px;padding:20px;text-align:center;margin:24px 0;'>
                <p style='color:#888;font-size:12px;margin:0 0 8px;text-transform:uppercase;letter-spacing:2px;'>Your OTP Code</p>
                <span style='font-size:40px;font-weight:800;color:#2563eb;letter-spacing:10px;'>$otp</span>
            </div>
            <p style='color:#888;font-size:12px;'>
                If you did not request this, you can safely ignore this email.
                Your password will not be changed.
            </p>
            <hr style='border:none;border-top:1px solid #dde2ee;margin:20px 0;'>
            <p style='color:#aaa;font-size:11px;text-align:center;'>
                &copy; " . date('Y') . " Student Assist. All rights reserved.
            </p>
        </div>";
        $mail->AltBody = "Your Student Assist password reset OTP is: $otp\n\nThis code is valid for 10 minutes.\n\nIf you did not request this, ignore this email.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
