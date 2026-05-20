<?php
/**
 * OTP Test Page
 * This page shows OTP configuration status and allows testing OTP functionality
 */

// Load PHPMailer classes
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables from .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Check PHPMailer configuration
$smtpUser = trim((string) (getenv('GMAIL_SMTP_USER') ?: (isset($_ENV['GMAIL_SMTP_USER']) ? $_ENV['GMAIL_SMTP_USER'] : '')));
$smtpPass = trim((string) (getenv('GMAIL_SMTP_PASS') ?: (isset($_ENV['GMAIL_SMTP_PASS']) ? $_ENV['GMAIL_SMTP_PASS'] : '')));
$fromName = trim((string) (getenv('GMAIL_FROM_NAME') ?: (isset($_ENV['GMAIL_FROM_NAME']) ? $_ENV['GMAIL_FROM_NAME'] : 'VapeShop System')));

$emailConfigured = !empty($smtpUser) && !empty($smtpPass);

// Test OTP generation
$testResult = null;
$testEmail = '';
$testOtp = '';
$smtpDebug = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_email'])) {
    $testEmail = filter_var($_POST['test_email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        // Generate test OTP
        $otp = (string) random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minutes default
        
        // Try to send email if configured
        $emailSent = false;
        $emailError = null;
        
        if ($emailConfigured) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $smtpUser;
                $mail->Password = $smtpPass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->SMTPDebug = 2; // Enable verbose debug output
                $mail->Debugoutput = function($str, $level) {
                    // Capture debug info
                    global $smtpDebug;
                    $smtpDebug .= $str . "\n";
                };
                $mail->setFrom($smtpUser, $fromName);
                $mail->addAddress($testEmail);
                $mail->isHTML(true);
                $mail->Subject = 'Test OTP Code';
                $mail->Body = '<p>Your test OTP code is:</p><h2 style="letter-spacing:4px">' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</h2><p>This code expires in 5 minute(s).</p>';
                $mail->AltBody = "Your test OTP code is: {$otp}\nThis code expires in 5 minute(s).";
                
                $mail->send();
                $emailSent = true;
            } catch (Exception $e) {
                $emailError = 'SMTP Error: ' . $e->getMessage() . ' | Error Info: ' . $mail->ErrorInfo;
            } catch (\Throwable $e) {
                $emailError = 'Email error: ' . $e->getMessage();
            }
        }
        
        $testResult = [
            'sent' => $emailSent,
            'error' => $emailError,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'debug' => $smtpDebug,
            'smtp_user' => $smtpUser,
            'smtp_pass_length' => strlen($smtpPass)
        ];
        $testOtp = $otp;
    } else {
        $testResult = ['error' => 'Invalid email address'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Test Page</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            margin-top: 0;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .status-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .status-connected {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .status-disconnected {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .status-item {
            margin: 10px 0;
            padding: 8px;
            background: white;
            border-radius: 4px;
        }
        .label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 150px;
        }
        .value {
            color: #333;
        }
        .test-form {
            background: #f0f4f8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5568d3;
        }
        .result-box {
            margin: 20px 0;
            padding: 15px;
            border-radius: 6px;
        }
        .result-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .otp-display {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            text-align: center;
            padding: 20px;
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            margin: 10px 0;
        }
        .config-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 6px;
            margin: 10px 0;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 OTP System Test Page</h1>
        
        <!-- Email Configuration Status -->
        <div class="status-card <?php echo $emailConfigured ? 'status-connected' : 'status-disconnected'; ?>">
            <h2>📧 PHPMailer Email Configuration Status</h2>
            <div class="status-item">
                <span class="label">Status:</span>
                <span class="value">
                    <?php if ($emailConfigured): ?>
                        <span class="badge badge-success">CONNECTED</span>
                    <?php else: ?>
                        <span class="badge badge-danger">NOT CONFIGURED</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="label">SMTP User:</span>
                <span class="value"><?php echo $smtpUser ? '✓ Configured' : '✗ Not set'; ?></span>
            </div>
            <div class="status-item">
                <span class="label">SMTP Password:</span>
                <span class="value"><?php echo $smtpPass ? '✓ Configured' : '✗ Not set'; ?></span>
            </div>
            <div class="status-item">
                <span class="label">From Name:</span>
                <span class="value"><?php echo htmlspecialchars($fromName); ?></span>
            </div>
        </div>

        <!-- OTP Configuration -->
        <div class="status-card">
            <h2>⚙️ OTP Configuration</h2>
            <div class="status-item">
                <span class="label">TTL (Time to Live):</span>
                <span class="value">300 seconds (5 minutes)</span>
            </div>
            <div class="status-item">
                <span class="label">Max Attempts:</span>
                <span class="value">3</span>
            </div>
            <div class="status-item">
                <span class="label">Resend Cooldown:</span>
                <span class="value">60 seconds</span>
            </div>
        </div>

        <!-- Test OTP Generation -->
        <div class="test-form">
            <h2>🧪 Test OTP Generation</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="test_email">Enter Email Address for Testing:</label>
                    <input type="email" id="test_email" name="test_email" placeholder="test@example.com" required value="<?php echo htmlspecialchars($testEmail); ?>">
                </div>
                <button type="submit">Generate & Send Test OTP</button>
            </form>

            <?php if ($testResult): ?>
                <div class="result-box <?php echo $testResult['sent'] ? 'result-success' : 'result-error'; ?>">
                    <h3><?php echo $testResult['sent'] ? '✓ OTP Sent Successfully' : '✗ OTP Send Failed'; ?></h3>
                    
                    <?php if ($testResult['sent']): ?>
                        <p><strong>Email sent to:</strong> <?php echo htmlspecialchars($testEmail); ?></p>
                        <p><strong>Expires at:</strong> <?php echo htmlspecialchars($testResult['expires_at']); ?></p>
                        
                        <?php if (!$emailConfigured): ?>
                            <div class="config-info">
                                <p><strong>⚠️ Email is not configured - using test mode</strong></p>
                                <p>Since PHPMailer is not configured, the OTP is shown below for testing purposes:</p>
                                <div class="otp-display"><?php echo htmlspecialchars($testOtp); ?></div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><strong>Error:</strong> <?php echo htmlspecialchars($testResult['error']); ?></p>
                        
                        <?php if (isset($testResult['smtp_user'])): ?>
                            <div class="config-info">
                                <p><strong>🔍 SMTP Configuration Details:</strong></p>
                                <ul>
                                    <li><strong>SMTP User:</strong> <?php echo htmlspecialchars($testResult['smtp_user']); ?></li>
                                    <li><strong>Password Length:</strong> <?php echo $testResult['smtp_pass_length']; ?> characters</li>
                                    <li><strong>Password Status:</strong> <?php echo $testResult['smtp_pass_length'] >= 16 ? '✓ Appears to be an App Password (16+ chars)' : '⚠️ Too short - likely not an App Password'; ?></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($testResult['debug']) && !empty($testResult['debug'])): ?>
                            <div class="config-info">
                                <p><strong>🔧 SMTP Debug Information:</strong></p>
                                <pre style="background: #2d2d2d; color: #f8f8f2; padding: 10px; border-radius: 4px; overflow-x: auto; max-height: 300px;"><?php echo htmlspecialchars($testResult['debug']); ?></pre>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$emailConfigured): ?>
                            <div class="config-info">
                                <p><strong>⚠️ Email is not configured - showing OTP for testing</strong></p>
                                <p>Since PHPMailer is not configured, the OTP is shown below for testing purposes:</p>
                                <div class="otp-display"><?php echo htmlspecialchars($testOtp); ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Setup Instructions -->
        <div class="status-card">
            <h2>📋 Setup Instructions</h2>
            <div class="config-info">
                <p><strong>To configure PHPMailer for email sending:</strong></p>
                <ol>
                    <li>Open your <code>.env</code> file in the project root</li>
                    <li>Add or update these lines:
                        <pre style="background: #2d2d2d; color: #f8f8f2; padding: 10px; border-radius: 4px; overflow-x: auto;">
GMAIL_SMTP_USER=your-email@gmail.com
GMAIL_SMTP_PASS=your-app-password
GMAIL_FROM_NAME=VapeShop System</pre>
                    </li>
                    <li>For Gmail, you need to:
                        <ul>
                            <li>Enable 2-Step Verification on your Google Account</li>
                            <li>Generate an App Password (Google Account → Security → App passwords)</li>
                            <li>Use the 16-character App Password (not your regular password)</li>
                        </ul>
                    </li>
                    <li>Restart your server after changing .env</li>
                </ol>
            </div>
        </div>

        <p style="text-align: center; color: #666; margin-top: 20px;">
            <a href="../public/login" style="color: #667eea; text-decoration: none;">← Back to Login</a>
        </p>
    </div>
</body>
</html>
