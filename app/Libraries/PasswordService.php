<?php

namespace App\Libraries;

class PasswordService
{
    /**
     * @var int|string
     */
    private static $algorithm;

    /**
     * @return int|string
     */
    private static function algorithm()
    {
        if (self::$algorithm !== null) {
            return self::$algorithm;
        }

        $preferred = strtolower((string) env('AUTH_PASSWORD_ALGO', 'argon2id'));
        $argonAvailable = defined('PASSWORD_ARGON2ID');

        if ($argonAvailable && $preferred !== 'bcrypt') {
            self::$algorithm = PASSWORD_ARGON2ID;
            return self::$algorithm;
        }

        self::$algorithm = PASSWORD_BCRYPT;

        return self::$algorithm;
    }

    /**
     * @return array<string, int>
     */
    private static function options(): array
    {
        if (self::algorithm() === PASSWORD_ARGON2ID) {
            return [
                'memory_cost' => (int) env('AUTH_ARGON_MEMORY_COST', 65536),
                'time_cost' => (int) env('AUTH_ARGON_TIME_COST', 4),
                'threads' => (int) env('AUTH_ARGON_THREADS', 2),
            ];
        }

        return [
            'cost' => (int) env('AUTH_BCRYPT_COST', 12),
        ];
    }

    public static function hash(string $plainText): string
    {
        return password_hash($plainText, self::algorithm(), self::options());
    }

    public static function verify(string $plainText, string $hash): bool
    {
        return password_verify($plainText, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, self::algorithm(), self::options());
    }

    public static function algorithmLabel(): string
    {
        return self::algorithm() === PASSWORD_ARGON2ID ? 'argon2id' : 'bcrypt';
    }
}
