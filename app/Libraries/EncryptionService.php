<?php

namespace App\Libraries;

use CodeIgniter\Encryption\Encryption;

class EncryptionService
{
    protected $encrypter;
    protected $key;

    public function __construct()
    {
        $this->encrypter = new Encryption();
        // Use a secure key from environment or generate one
        $this->key = $_ENV['encryption_key'] ?? $this->generateSecureKey();
        $this->encrypter->setKey($this->key);
    }

    /**
     * Encrypt sensitive data
     */
    public function encrypt(string $data): string
    {
        return $this->encrypter->encrypt($data);
    }

    /**
     * Decrypt sensitive data
     */
    public function decrypt(string $encryptedData): string
    {
        return $this->encrypter->decrypt($encryptedData);
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
     * Generate secure encryption key
     */
    private function generateSecureKey(): string
    {
        return base64_encode(random_bytes(32));
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
}
