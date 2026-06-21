<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerHelper {
    /**
     * Send a verification or notification code via email.
     */
    public static function sendCode(string $email, string $subject, int $code): bool {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST']     ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER']     ?? '';
            $mail->Password   = $_ENV['SMTP_PASS']     ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 465);
            $mail->CharSet    = 'UTF-8';

            // Recipients
            $from_email = $_ENV['SMTP_FROM']      ?? 'noreply@samvadhub.com';
            $from_name  = $_ENV['SMTP_FROM_NAME'] ?? 'SamvadHub';
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = self::buildEmailTemplate($subject, $code);
            $mail->AltBody = "Your SamvadHub verification code is: {$code}. It expires in 10 minutes.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Build HTML email body template.
     */
    private static function buildEmailTemplate(string $subject, int $code): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$subject}</title>
</head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background: #f4f4f4; margin:0; padding:20px;">
  <div style="max-width:480px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
    <div style="background: linear-gradient(135deg, #6C47FF 0%, #A855F7 100%); padding:32px; text-align:center;">
      <h1 style="color:#fff; margin:0; font-size:28px; letter-spacing:-0.5px;">SamvadHub</h1>
      <p style="color:rgba(255,255,255,0.85); margin:8px 0 0; font-size:14px;">Connect · Share · Engage</p>
    </div>
    <div style="padding:40px 32px;">
      <h2 style="color:#1a1a2e; font-size:20px; margin:0 0 12px;">{$subject}</h2>
      <p style="color:#555; font-size:15px; line-height:1.6; margin:0 0 28px;">
        Use the code below to proceed. This code is valid for <strong>10 minutes</strong>.
      </p>
      <div style="background:#f8f7ff; border:2px dashed #6C47FF; border-radius:12px; padding:24px; text-align:center; margin-bottom:28px;">
        <span style="font-size:36px; font-weight:700; letter-spacing:12px; color:#6C47FF; font-family:monospace;">{$code}</span>
      </div>
      <p style="color:#888; font-size:13px; margin:0;">
        If you didn't request this, please ignore this email or 
        <a href="mailto:support@samvadhub.com" style="color:#6C47FF;">contact support</a>.
      </p>
    </div>
    <div style="background:#f8f8f8; padding:20px 32px; text-align:center; border-top:1px solid #eee;">
      <p style="color:#aaa; font-size:12px; margin:0;">
        © 2025 SamvadHub — Built with ❤️ at NIET Greater Noida
      </p>
    </div>
  </div>
</body>
</html>
HTML;
    }
}
