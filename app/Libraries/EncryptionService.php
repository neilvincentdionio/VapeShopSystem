<?php

namespace App\Libraries;

class EncryptionService
{
    protected $encrypter;
    protected $key;
    protected bool $enabled = false;

    public function __construct()
    {
        $config = config('Encryption');
        $this->key = $this->resolveKey((string) ($config->key ?? ''));

        if ($this->key === '') {
            log_message('warning', 'Encryption key is not configured. Sensitive fields will be stored without encryption until a key is set.');
            $this->encrypter = null;
            return;
        }

        $config->key = $this->key;
        $this->encrypter = service('encrypter', $config);
        $this->enabled = true;
    }

    /**
     * Encrypt sensitive data
     */
    public function encrypt(string $data): string
    {
        if ($data === '') {
            return '';
        }

        if (!$this->enabled || $this->encrypter === null) {
            return $data;
        }

        return base64_encode($this->encrypter->encrypt($data));
    }

    /**
     * Decrypt sensitive data
     */
    public function decrypt(string $encryptedData): string
    {
        if ($encryptedData === '') {
            return '';
        }

        if (!$this->enabled || $this->encrypter === null) {
            return $encryptedData;
        }

        $decoded = base64_decode($encryptedData, true);
        if ($decoded === false) {
            return $encryptedData;
        }

        try {
            return (string) $this->encrypter->decrypt($decoded);
        } catch (\Throwable $e) {
            return $encryptedData;
        }
    }

    /**
     * Encrypt phone number
     */
    public function encryptPhoneNumber(string $phoneNumber): string
    {
        return $this->encrypt($phoneNumber);
    }

    /**
     * Decrypt phone number
     */
    public function decryptPhoneNumber(string $encryptedPhone): string
    {
        return $this->decrypt($encryptedPhone);
    }

    /**
     * Encrypt email (optional - for high security)
     */
    public function encryptEmail(string $email): string
    {
        return $this->encrypt($email);
    }

    /**
     * Decrypt email
     */
    public function decryptEmail(string $encryptedEmail): string
    {
        return $this->decrypt($encryptedEmail);
    }

    /**
     * Encrypt address data
     */
    public function encryptAddress(string $address): string
    {
        return $this->encrypt($address);
    }

    /**
     * Decrypt address data
     */
    public function decryptAddress(string $encryptedAddress): string
    {
        return $this->decrypt($encryptedAddress);
    }

    /**
     * Hash sensitive data for verification (non-reversible)
     */
    public function hashSensitiveData(string $data): string
    {
        return hash('sha256', $data . $this->key);
    }

    /**
     * Verify sensitive data hash
     */
    public function verifySensitiveDataHash(string $data, string $hash): bool
    {
        return hash_equals($hash, $this->hashSensitiveData($data));
    }

    /**
     * Mask sensitive data for display
     */
    public function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        if (strlen($username) <= 2) {
            $maskedUsername = str_repeat('*', strlen($username));
        } else {
            $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
        }
        
        return $maskedUsername . '@' . $domain;
    }

    /**
     * Mask phone number for display
     */
    public function maskPhoneNumber(string $phoneNumber): string
    {
        // Remove non-digit characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (strlen($cleanPhone) <= 4) {
            return str_repeat('*', strlen($cleanPhone));
        }
        
        $visible = substr($cleanPhone, -4);
        $masked = str_repeat('*', strlen($cleanPhone) - 4) . $visible;
        
        return $masked;
    }

    private function resolveKey(string $configuredKey): string
    {
        $candidate = trim($configuredKey);
        if ($candidate !== '') {
            return $candidate;
        }

        $envKey = trim((string) env('encryption.key', env('encryption_key', '')));
        if ($envKey !== '') {
            return $envKey;
        }

        $jwtSecret = trim((string) env('JWT_SECRET', ''));
        if ($jwtSecret !== '') {
            return $jwtSecret;
        }

        return '';
    }
}
