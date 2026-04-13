<?php

namespace App\Libraries;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

class JwtService
{
    private static ?string $secretKey = null;
    private static string $algorithm = 'HS256';

    private static function getSecretKey(): string
    {
        if (self::$secretKey !== null && self::$secretKey !== '') {
            return self::$secretKey;
        }

        $envSecret = env('JWT_SECRET');
        if (is_string($envSecret) && trim($envSecret) !== '') {
            self::$secretKey = trim($envSecret);
            return self::$secretKey;
        }

        $encryptionKey = config('Encryption')->key ?? '';
        if (is_string($encryptionKey) && trim($encryptionKey) !== '') {
            self::$secretKey = trim($encryptionKey);
            return self::$secretKey;
        }

        throw new \RuntimeException('JWT secret is not configured. Set JWT_SECRET in your environment.');
    }

    public static function accessTokenTtl(): int
    {
        return max(60, (int) env('JWT_ACCESS_TTL_SECONDS', 900));
    }

    public static function refreshTokenTtl(): int
    {
        return max(300, (int) env('JWT_REFRESH_TTL_SECONDS', 604800));
    }

    public static function issuer(): string
    {
        return (string) env('JWT_ISSUER', base_url('/'));
    }

    public static function audience(): string
    {
        return (string) env('JWT_AUDIENCE', 'vapeshop-api');
    }

    public static function generateAccessToken(array $payload): string
    {
        $now = time();

        $claims = array_merge($payload, [
            'iss' => self::issuer(),
            'aud' => self::audience(),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + self::accessTokenTtl(),
            'type' => 'access',
            'jti' => bin2hex(random_bytes(16)),
        ]);

        return JWT::encode($claims, self::getSecretKey(), self::$algorithm);
    }

    public static function generateRefreshToken(array $payload): string
    {
        $now = time();

        $claims = array_merge($payload, [
            'iss' => self::issuer(),
            'aud' => self::audience(),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + self::refreshTokenTtl(),
            'type' => 'refresh',
            'jti' => bin2hex(random_bytes(16)),
        ]);

        return JWT::encode($claims, self::getSecretKey(), self::$algorithm);
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecretKey(), self::$algorithm));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            log_message('notice', 'JWT token expired: {message}', ['message' => $e->getMessage()]);
            return null;
        } catch (SignatureInvalidException $e) {
            log_message('warning', 'JWT token signature invalid: {message}', ['message' => $e->getMessage()]);
            return null;
        } catch (\RuntimeException $e) {
            log_message('error', 'JWT configuration error: {message}', ['message' => $e->getMessage()]);
            return null;
        } catch (\Throwable $e) {
            log_message('warning', 'JWT token validation error: {message}', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public static function isAccessToken(?array $payload): bool
    {
        return is_array($payload) && ($payload['type'] ?? null) === 'access';
    }

    public static function isRefreshToken(?array $payload): bool
    {
        return is_array($payload) && ($payload['type'] ?? null) === 'refresh';
    }

    public static function getTokenFromRequest(): ?string
    {
        $request = service('request');
        $authHeader = $request->getHeaderLine('Authorization');

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return trim((string) $matches[1]);
        }

        return null;
    }

    /**
     * @return array{id:int|null,email:string|null,name:string|null,role:string|null,payload:array<string,mixed>}|null
     */
    public static function getCurrentUser(): ?array
    {
        $token = self::getTokenFromRequest();
        if ($token === null) {
            return null;
        }

        $payload = self::validateToken($token);
        if (!self::isAccessToken($payload)) {
            return null;
        }

        return [
            'id' => isset($payload['user_id']) ? (int) $payload['user_id'] : null,
            'email' => isset($payload['user_email']) ? (string) $payload['user_email'] : null,
            'name' => isset($payload['user_name']) ? (string) $payload['user_name'] : null,
            'role' => isset($payload['user_role']) ? (string) $payload['user_role'] : null,
            'payload' => $payload,
        ];
    }

    /**
     * @param array<string,mixed> $identity
     * @return array{access_token:string,refresh_token:string,token_type:string,expires_in:int,refresh_expires_in:int,refresh_jti:string,refresh_expires_at:int}
     */
    public static function issueTokenPair(array $identity): array
    {
        $accessToken = self::generateAccessToken($identity);
        $refreshToken = self::generateRefreshToken($identity);

        $refreshPayload = self::validateToken($refreshToken) ?? [];

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => self::accessTokenTtl(),
            'refresh_expires_in' => self::refreshTokenTtl(),
            'refresh_jti' => (string) ($refreshPayload['jti'] ?? ''),
            'refresh_expires_at' => (int) ($refreshPayload['exp'] ?? (time() + self::refreshTokenTtl())),
        ];
    }

    public static function setSecretKey(string $key): void
    {
        self::$secretKey = $key;
    }
}
