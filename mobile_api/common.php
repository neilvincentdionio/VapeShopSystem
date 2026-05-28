<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once dirname(__DIR__) . '/app/Helpers/input_validation_helper.php';

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
 * Reject registration/profile text that contains unsupported special characters.
 *
 * Each item: ['value' => string, 'type' => validation type]
 *
 * @param list<array{value: string, type: string}> $fields
 */
function assert_safe_text_fields(array $fields): void
{
    $messages = safe_input_messages();

    foreach ($fields as $field) {
        if (! is_array($field)) {
            continue;
        }

        $value = trim((string) ($field['value'] ?? ''));
        $type = trim((string) ($field['type'] ?? ''));

        if ($value === '' || $type === '' || matches_safe_input($value, $type)) {
            continue;
        }

        json_response(false, $messages[$type] ?? 'One or more fields contain unsupported characters.', null, 400);
    }
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
 * Load CodeIgniter services/config without running the web app or Spark CLI.
 */
/**
 * Save uploaded verification ID image (multipart field: verification_id_image).
 */
function mobile_store_verification_id_upload(): ?string
{
    if (!isset($_FILES['verification_id_image']) || !is_array($_FILES['verification_id_image'])) {
        return null;
    }

    $file = $_FILES['verification_id_image'];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return null;
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > 3 * 1024 * 1024) {
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!is_string($mimeType) || !isset($allowed[$mimeType])) {
        return null;
    }

    $uploadDirectory = dirname(__DIR__) . '/writable/uploads/customer_ids';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
        return null;
    }

    try {
        $fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mimeType];
        $targetPath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($tmpName, $targetPath)) {
            return null;
        }

        return 'customer_ids/' . $fileName;
    } catch (Throwable $e) {
        error_log('mobile_store_verification_id_upload failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Notify admins that a customer account needs approval (mobile registration).
 */
function mobile_notify_account_pending(int $userId, string $customerName): void
{
    if ($userId <= 0) {
        return;
    }

    try {
        mobile_ci_bootstrap();
        $notificationService = new \App\Libraries\NotificationService();
        $customerName = trim($customerName);
        if ($customerName === '') {
            $customerName = 'A customer';
        }

        $notificationService->notifyAdmins([
            'category' => 'approvals',
            'type' => 'account_pending',
            'title' => 'Customer approval needed',
            'message' => $customerName . ' submitted an account request (mobile app).',
            'link' => site_url('user-management/view/' . $userId),
            'related_type' => 'user',
            'related_id' => $userId,
        ]);
    } catch (Throwable $e) {
        error_log('mobile_notify_account_pending failed: ' . $e->getMessage());
    }
}

function mobile_ci_bootstrap(): void
{
    static $booted = false;

    if ($booted) {
        return;
    }

    $booted = true;
    $root = dirname(__DIR__);

    if (! defined('FCPATH')) {
        define('FCPATH', $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
    }

    if (! class_exists(\CodeIgniter\Boot::class, false)) {
        require $root . '/vendor/autoload.php';
        require $root . '/app/Config/Paths.php';
        $paths = new \Config\Paths();
        require $paths->systemDirectory . '/Boot.php';
        \CodeIgniter\Boot::bootWorker($paths);
    }
}

/**
 * Bootstrap CodeIgniter encrypter for decrypting user address/profile fields.
 */
function mobile_encryption_service(): ?\App\Libraries\EncryptionService
{
    static $service = null;
    static $attempted = false;

    if ($attempted) {
        return $service;
    }

    $attempted = true;

    try {
        mobile_ci_bootstrap();
        $service = new \App\Libraries\EncryptionService();
    } catch (Throwable $e) {
        error_log('mobile_encryption_service failed: ' . $e->getMessage());
        $service = null;
    }

    return $service;
}

/**
 * Decrypt a stored phone or address field (no-op when value is already plain text).
 */
function mobile_decrypt_sensitive(string $value, string $type = 'address'): string
{
    if ($value === '') {
        return '';
    }

    $service = mobile_encryption_service();
    if ($service === null) {
        return $value;
    }

    return $type === 'phone'
        ? $service->decryptPhoneNumber($value)
        : $service->decryptAddress($value);
}

/**
 * Encrypt a stored phone or address field for database writes.
 */
function mobile_encrypt_sensitive(string $value, string $type = 'address'): string
{
    if ($value === '') {
        return '';
    }

    $service = mobile_encryption_service();
    if ($service === null) {
        return $value;
    }

    return $type === 'phone'
        ? $service->encryptPhoneNumber($value)
        : $service->encryptAddress($value);
}

/**
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function mobile_decrypt_address_row(array $row): array
{
    foreach (['address_line', 'city', 'barangay', 'province', 'postal_code', 'country'] as $field) {
        if (array_key_exists($field, $row) && $row[$field] !== null && (string) $row[$field] !== '') {
            $row[$field] = mobile_decrypt_sensitive((string) $row[$field], 'address');
        }
    }

    return $row;
}

/**
 * @param array<string, mixed> $address
 */
function mobile_format_address_parts(array $address): string
{
    $parts = [];
    foreach (['address_line', 'barangay', 'city', 'province', 'postal_code', 'country'] as $field) {
        $value = trim((string) ($address[$field] ?? ''));
        if ($value !== '') {
            $parts[] = $value;
        }
    }

    return implode(', ', $parts);
}

/**
 * Build readable shipping address from user primary address.
 */
function build_shipping_address(PDO $db, int $userId): string
{
    $address = get_user_primary_address($db, $userId);

    return $address === null ? '' : mobile_format_address_parts($address);
}

/**
 * Prefer decrypted DB address when client sent ciphertext from stale local cache.
 */
function mobile_normalize_shipping_address(string $address, PDO $db, int $userId): string
{
    $address = trim($address);

    // The mobile app stores address parts and joins them with `, `.
    // If decrypting fails for saved parts, the resulting full string contains spaces/commas,
    // which makes `is_probably_encrypted_text($address)` return false.
    // So we check each comma-separated part individually.
    $looksEncrypted = false;
    if ($address !== '') {
        $parts = array_map(static fn ($p): string => trim((string) $p), explode(',', $address));
        foreach ($parts as $part) {
            if ($part !== '' && is_probably_encrypted_text($part)) {
                $looksEncrypted = true;
                break;
            }
        }
    }

    if ($address === '' || $looksEncrypted) {
        $fromDb = build_shipping_address($db, $userId);
        if ($fromDb !== '') {
            return $fromDb;
        }
    }

    return $address;
}

/**
 * @return array<string, mixed>|null
 */
function get_user_primary_address(PDO $db, int $userId): ?array
{
    $stmt = $db->prepare(
        'SELECT address_line, city, barangay, province, postal_code, country,
                delivery_latitude, delivery_longitude
         FROM user_addresses
         WHERE user_id = :user_id
         ORDER BY is_primary DESC, id ASC
         LIMIT 1'
    );
    $stmt->execute([':user_id' => $userId]);
    $address = $stmt->fetch();

    return is_array($address) ? mobile_decrypt_address_row($address) : null;
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

if (!function_exists('mobile_format_capacity')) {
    function mobile_format_capacity(string $category, int $value): string
    {
        if ($value <= 0) {
            return '';
        }

        $slug = strtolower(str_replace([' ', '_'], '', $category));

        if (str_contains($slug, 'eliquid') || str_contains($slug, 'liquid')) {
            return $value . 'ML';
        }

        return number_format($value) . ' Puffs';
    }
}

if (!function_exists('mobile_build_spec')) {
    function mobile_build_spec(
        string $category,
        int $puffs,
        string $nicotineLevel = '',
        int $batteryCapacity = 0,
        int $eliquidCapacity = 0
    ): string {
        $category = trim($category);
        $slug = strtolower(str_replace([' ', '_'], '-', $category));
        $nicotineLevel = trim($nicotineLevel);
        $parts = [$category];

        if (str_contains($slug, 'disposable')) {
            if ($puffs > 0) {
                $parts[] = number_format($puffs) . ' Puffs';
            }
            if ($batteryCapacity > 0) {
                $parts[] = number_format($batteryCapacity) . 'mAh';
            }
            if ($eliquidCapacity > 0) {
                $parts[] = $eliquidCapacity . 'ML E-Liquid';
            }
        } else {
            $specLabel = mobile_format_capacity($category, $puffs);
            if ($specLabel !== '') {
                $parts[] = $specLabel;
            }
        }

        if ($nicotineLevel !== '') {
            $parts[] = $nicotineLevel;
        }

        return implode(' • ', array_filter($parts, static fn (string $part): bool => $part !== ''));
    }
}

if (!function_exists('mobile_effective_product_price')) {
    function mobile_effective_product_price(float $sellingPrice, float $legacyPrice): float
    {
        if ($sellingPrice > 0) {
            return round($sellingPrice, 2);
        }

        return round(max(0.0, $legacyPrice), 2);
    }
}

if (!function_exists('mobile_effective_variant_price')) {
    function mobile_effective_variant_price(?float $variantPrice, float $sellingPrice, float $legacyPrice): float
    {
        if ($variantPrice !== null && $variantPrice > 0) {
            return round($variantPrice, 2);
        }

        return mobile_effective_product_price($sellingPrice, $legacyPrice);
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

