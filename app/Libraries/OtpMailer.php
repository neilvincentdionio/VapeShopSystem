<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class OtpMailer
{
    /**
     * @return array{sent: bool, error: string|null}
     */
    public function sendOtp(string $toEmail, string $otpCode): array
    {
        $smtpUser = (string) getenv('GMAIL_SMTP_USER');
        $smtpPass = (string) getenv('GMAIL_SMTP_PASS');
        $fromName = (string) (getenv('GMAIL_FROM_NAME') ?: 'VapeShop System');

        if ($smtpUser === '' || $smtpPass === '') {
            return ['sent' => false, 'error' => 'Gmail SMTP not configured (set GMAIL_SMTP_USER and GMAIL_SMTP_PASS).'];
        }

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
            $mail->Body    = '<p>Your OTP code is:</p><h2 style="letter-spacing:4px">' . htmlspecialchars($otpCode) . '</h2><p>This code expires in 5 minutes.</p>';
            $mail->AltBody = "Your OTP code is: {$otpCode}\nThis code expires in 5 minutes.";

            $mail->send();

            return ['sent' => true, 'error' => null];
        } catch (PHPMailerException $e) {
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }
}

