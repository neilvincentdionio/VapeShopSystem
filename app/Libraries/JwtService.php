<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtService
{
    private static $secretKey = 'your-super-secret-jwt-key-change-in-production';
    private static $algorithm = 'HS256';
    private static $accessTokenExpiry = 3600; // 1 hour
    private static $refreshTokenExpiry = 604800; // 7 days

    /**
     * Generate access token
     */
    public static function generateAccessToken(array $payload): string
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + self::$accessTokenExpiry;
        $payload['type'] = 'access';
        
        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    /**
     * Generate refresh token
     */
    public static function generateRefreshToken(array $payload): string
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + self::$refreshTokenExpiry;
        $payload['type'] = 'refresh';
        
        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    /**
     * Validate and decode token
     */
    public static function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            log_message('error', 'JWT Token expired: ' . $e->getMessage());
            return null;
        } catch (SignatureInvalidException $e) {
            log_message('error', 'JWT Token signature invalid: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            log_message('error', 'JWT Token validation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if token is access token
     */
    public static function isAccessToken(?array $payload): bool
    {
        return $payload && isset($payload['type']) && $payload['type'] === 'access';
    }

    /**
     * Check if token is refresh token
     */
    public static function isRefreshToken(?array $payload): bool
    {
        return $payload && isset($payload['type']) && $payload['type'] === 'refresh';
    }

    /**
     * Get token from Authorization header
     */
    public static function getTokenFromRequest(): ?string
    {
        $request = service('request');
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Get user from current token
     */
    public static function getCurrentUser(): ?array
    {
        $token = self::getTokenFromRequest();
        if (!$token) {
            return null;
        }

        $payload = self::validateToken($token);
        if (!$payload || !self::isAccessToken($payload)) {
            return null;
        }

        return [
            'id' => $payload['user_id'] ?? null,
            'email' => $payload['user_email'] ?? null,
            'name' => $payload['user_name'] ?? null,
            'role' => $payload['user_role'] ?? null
        ];
    }

    /**
     * Refresh access token using refresh token
     */
    public static function refreshAccessToken(string $refreshToken): ?string
    {
        $payload = self::validateToken($refreshToken);
        
        if (!$payload || !self::isRefreshToken($payload)) {
            return null;
        }

        // Create new access token with same user data
        $newPayload = [
            'user_id' => $payload['user_id'] ?? null,
            'user_email' => $payload['user_email'] ?? null,
            'user_name' => $payload['user_name'] ?? null,
            'user_role' => $payload['user_role'] ?? null
        ];

        return self::generateAccessToken($newPayload);
    }

    /**
     * Set secret key (for configuration)
     */
    public static function setSecretKey(string $key): void
    {
        self::$secretKey = $key;
    }

    /**
     * Set token expiry times
     */
    public static function setTokenExpiry(int $accessExpiry, int $refreshExpiry): void
    {
        self::$accessTokenExpiry = $accessExpiry;
        self::$refreshTokenExpiry = $refreshExpiry;
    }
}
