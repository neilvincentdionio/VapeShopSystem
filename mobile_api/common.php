<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Output a strict JSON response and stop execution.
 */
function json_response(bool $success, string $message, ?array $data = null, int $statusCode = 200): void
{
    http_response_code($statusCode);

    $payload = [
        'success' => $success,
        'message' => $message,
    ];

    if ($data !== null) {
        $payload['data'] = $data;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Enforce POST-only request.
 */
function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        json_response(false, 'Method not allowed. Use POST.', null, 405);
    }
}

/**
 * Parse incoming JSON or form-data payload as array.
 */
function get_request_data(): array
{
    $contentType = strtolower(trim((string) ($_SERVER['CONTENT_TYPE'] ?? '')));
    $rawInput = file_get_contents('php://input');

    if ($rawInput !== false && $rawInput !== '' && strpos($contentType, 'application/json') !== false) {
        $decoded = json_decode($rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            json_response(false, 'Invalid JSON payload.', null, 400);
        }
        return $decoded;
    }

    return is_array($_POST) ? $_POST : [];
}

/**
 * Ensure required fields are present and not empty.
 */
function require_fields(array $data, array $requiredFields): void
{
    $missing = [];

    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $data) || trim((string) $data[$field]) === '') {
            $missing[] = $field;
        }
    }

    if ($missing !== []) {
        json_response(false, 'Missing required fields: ' . implode(', ', $missing) . '.', null, 400);
    }
}

/**
 * Basic email validation with normalized result.
 */
function normalize_email(string $email): string
{
    $normalized = strtolower(trim($email));
    if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Invalid email address.', null, 400);
    }

    return $normalized;
}

/**
 * Find user by email.
 */
function find_user_by_email(PDO $db, string $email): ?array
{
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    return is_array($user) ? $user : null;
}

/**
 * Build readable shipping address from user primary address.
 */
function build_shipping_address(PDO $db, int $userId): string
{
    $stmt = $db->prepare(
        'SELECT address_line, city, barangay, province, postal_code, country
         FROM user_addresses
         WHERE user_id = :user_id
         ORDER BY is_primary DESC, id ASC
         LIMIT 1'
    );
    $stmt->execute([':user_id' => $userId]);
    $address = $stmt->fetch();

    if (!is_array($address)) {
        return '';
    }

    $parts = [];
    foreach (['address_line', 'city', 'barangay', 'province', 'postal_code', 'country'] as $field) {
        $value = trim((string) ($address[$field] ?? ''));
        if ($value !== '' && !is_probably_encrypted_text($value)) {
            $parts[] = $value;
        }
    }

    return implode(', ', $parts);
}

/**
 * Heuristic check for ciphertext-like values stored in legacy rows.
 */
function is_probably_encrypted_text(string $value): bool
{
    $len = strlen($value);
    if ($len < 60) {
        return false;
    }

    // Mostly base64-like chars and no spaces usually indicates encrypted blob.
    $base64Like = preg_match('/^[A-Za-z0-9+\/=._-]+$/', $value) === 1;
    $hasSpaces = strpos($value, ' ') !== false;
    return $base64Like && !$hasSpaces;
}

if (!function_exists('mobile_has_variant_table')) {
    function mobile_has_variant_table(PDO $db): bool
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        try {
            $stmt = $db->query("SHOW TABLES LIKE 'product_variants'");
            $cached = $stmt !== false && $stmt->fetch() !== false;
        } catch (Throwable $e) {
            $cached = false;
        }

        return $cached;
    }
}

if (!function_exists('mobile_uses_flavor_selection')) {
    function mobile_uses_flavor_selection(string $category): bool
    {
        return in_array(strtolower(trim($category)), ['pods', 'disposable', 'e-liquid'], true);
    }
}

if (!function_exists('mobile_format_puffs')) {
    function mobile_format_puffs(int $puffs): string
    {
        return $puffs > 0 ? number_format($puffs) . ' Puffs' : '';
    }
}

if (!function_exists('mobile_build_spec')) {
    function mobile_build_spec(string $category, int $puffs): string
    {
        $category = trim($category);
        $puffLabel = mobile_format_puffs($puffs);
        if ($category === '') {
            return $puffLabel;
        }
        if ($puffLabel === '') {
            return $category;
        }

        return $category . ' • ' . $puffLabel;
    }
}

if (!function_exists('compute_mobile_order_line')) {
    /**
     * @return array{quantity:int,unit_price:float,selling_price:float,subtotal:float,profit:float}
     */
    function compute_mobile_order_line(int $quantity, float $unitCost, float $sellingPrice): array
    {
        $quantity = max(0, $quantity);
        $sellingPrice = round($sellingPrice, 2);
        $unitCost = round($unitCost, 2);
        if ($unitCost <= 0 && $sellingPrice > 0) {
            $unitCost = round(max(0.0, $sellingPrice - 50.0), 2);
        }
        $subtotal = round($sellingPrice * $quantity, 2);
        $capital = round($unitCost * $quantity, 2);

        return [
            'quantity' => $quantity,
            'unit_price' => $unitCost,
            'selling_price' => $sellingPrice,
            'subtotal' => $subtotal,
            'profit' => round($subtotal - $capital, 2),
        ];
    }
}

