<?php
declare(strict_types=1);

function mobile_parse_latitude(mixed $value): ?float
{
    if ($value === null || $value === '' || ! is_numeric($value)) {
        return null;
    }
    $f = (float) $value;

    return ($f >= -90.0 && $f <= 90.0) ? $f : null;
}

function mobile_parse_longitude(mixed $value): ?float
{
    if ($value === null || $value === '' || ! is_numeric($value)) {
        return null;
    }
    $f = (float) $value;

    return ($f >= -180.0 && $f <= 180.0) ? $f : null;
}

function mobile_build_address_from_parts(array $parts): string
{
    $fields = ['delivery_address_line', 'delivery_country', 'delivery_province', 'delivery_city', 'delivery_barangay', 'delivery_postal_code'];
    $ordered = [];
    foreach ($fields as $field) {
        $value = trim((string) ($parts[$field] ?? ''));
        if ($value !== '') {
            $ordered[] = $value;
        }
    }

    return implode(', ', $ordered);
}

/**
 * @return array{shipping_address: string, delivery_latitude: float, delivery_longitude: float}|null
 */
function mobile_resolve_checkout_delivery(PDO $db, int $userId, array $input): ?array
{
    $mode = strtolower(trim((string) ($input['delivery_address_mode'] ?? 'manual')));
    $lat = mobile_parse_latitude($input['delivery_latitude'] ?? $input['latitude'] ?? null);
    $lng = mobile_parse_longitude($input['delivery_longitude'] ?? $input['longitude'] ?? null);

    if ($mode === 'saved_address' || $mode === 'use_my_address' || $mode === 'saved') {
        $address = mobile_normalize_shipping_address(
            trim((string) ($input['shipping_address'] ?? '')),
            $db,
            $userId
        );
        if ($address === '') {
            return null;
        }
        if ($lat === null || $lng === null) {
            $lat = mobile_parse_latitude($input['customer_latitude'] ?? null);
            $lng = mobile_parse_longitude($input['customer_longitude'] ?? null);
        }
        if ($lat === null || $lng === null) {
            return null;
        }

        return [
            'shipping_address' => $address,
            'delivery_latitude' => $lat,
            'delivery_longitude' => $lng,
        ];
    }

    $parts = [
        'delivery_address_line' => $input['delivery_address_line'] ?? $input['street'] ?? '',
        'delivery_country' => $input['delivery_country'] ?? $input['country'] ?? 'Philippines',
        'delivery_province' => $input['delivery_province'] ?? $input['province'] ?? '',
        'delivery_city' => $input['delivery_city'] ?? $input['city'] ?? '',
        'delivery_barangay' => $input['delivery_barangay'] ?? $input['barangay'] ?? '',
        'delivery_postal_code' => $input['delivery_postal_code'] ?? $input['postal_code'] ?? '',
    ];

    $address = mobile_build_address_from_parts($parts);
    if ($address === '' || count(array_filter($parts, static fn ($v): bool => trim((string) $v) !== '')) < 5) {
        return null;
    }
    if ($lat === null || $lng === null) {
        return null;
    }

    return [
        'shipping_address' => $address,
        'delivery_latitude' => $lat,
        'delivery_longitude' => $lng,
    ];
}

function mobile_store_shipment_defaults(): array
{
    return [
        'store_latitude' => 6.1128,
        'store_longitude' => 125.1717,
        'store_address' => 'QuickPuff Store, General Santos City',
    ];
}
