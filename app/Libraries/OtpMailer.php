<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class OtpMailer
{
    /**
     * @return array{sent: bool, error: string|null}
     */
    public function sendOtp(string $toEmail, string $otpCode, int $ttlMinutes = 5): array
    {
        $smtpUser = trim((string) env('GMAIL_SMTP_USER', getenv('GMAIL_SMTP_USER') ?: ''));
        $smtpPass = trim((string) env('GMAIL_SMTP_PASS', getenv('GMAIL_SMTP_PASS') ?: ''));
        $fromName = trim((string) env('GMAIL_FROM_NAME', getenv('GMAIL_FROM_NAME') ?: 'VapeShop System'));

        if ($smtpUser === '' || $smtpPass === '') {
            return ['sent' => false, 'error' => 'Gmail SMTP not configured (set GMAIL_SMTP_USER and GMAIL_SMTP_PASS in .env).'];
        }

        $ttlMinutes = max(1, $ttlMinutes);
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($smtpUser, $fromName);
            $mail->addAddress($toEmail);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = '<p>Your OTP code is:</p><h2 style="letter-spacing:4px">' . htmlspecialchars($otpCode, ENT_QUOTES, 'UTF-8') . '</h2><p>This code expires in ' . $ttlMinutes . ' minute(s).</p>';
            $mail->AltBody = "Your OTP code is: {$otpCode}\nThis code expires in {$ttlMinutes} minute(s).";

            $mail->send();

            return ['sent' => true, 'error' => null];
        } catch (PHPMailerException $e) {
            return ['sent' => false, 'error' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['sent' => false, 'error' => 'Email error: ' . $e->getMessage()];
        }
    }
}

