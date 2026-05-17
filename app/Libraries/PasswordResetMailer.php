<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class PasswordResetMailer
{
    /**
     * @return array{sent: bool, error: string|null}
     */
    public function sendResetLink(string $toEmail, string $resetLink): array
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
            $mail->Subject = 'Reset Your VapeShop Password';
            $mail->Body = '<p>Hello,</p>'
                . '<p>We received a request to reset your VapeShop password.</p>'
                . '<p><a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">Click here to reset your password</a></p>'
                . '<p>This link expires in 1 hour.</p>'
                . '<p>If you did not request this, you can ignore this email.</p>';
            $mail->AltBody = "We received a request to reset your VapeShop password.\n\n"
                . "Open this link to reset your password:\n{$resetLink}\n\n"
                . "This link expires in 1 hour.\n"
                . "If you did not request this, you can ignore this email.";

            $mail->send();

            return ['sent' => true, 'error' => null];
        } catch (PHPMailerException $e) {
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }
}

