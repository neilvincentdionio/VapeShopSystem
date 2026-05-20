<?php

use App\Models\ProductModel;

if (! function_exists('normalize_product_category')) {
    /**
     * Map stored category labels to the canonical option used in forms and filters.
     */
    function normalize_product_category($category): string
    {
        $category = trim((string) ($category ?? ''));
        if ($category === '') {
            return 'Uncategorized';
        }

        $slug = strtolower(str_replace([' ', '_'], '-', $category));
        $compact = preg_replace('/[^a-z0-9]+/', '', $slug) ?? $slug;

        $map = [
            'device' => 'Device',
            'devices' => 'Device',
            'pods' => 'Pods',
            'pod' => 'Pods',
            'e-liquid' => 'E-Liquid',
            'eliquid' => 'E-Liquid',
            'disposable' => 'Disposable',
            'disposables' => 'Disposable',
        ];

        if (isset($map[$slug])) {
            return $map[$slug];
        }

        if (isset($map[$compact])) {
            return $map[$compact];
        }

        foreach (ProductModel::CATEGORY_OPTIONS as $option) {
            if (strcasecmp($category, $option) === 0) {
                return $option;
            }
        }

        return $category;
    }
}

if (! function_exists('product_category_slug')) {
    function product_category_slug(string $category): string
    {
        return strtolower(str_replace(' ', '-', normalize_product_category($category)));
    }
}

if (! function_exists('product_nicotine_level_options')) {
    /**
     * @return list<string>
     */
    function product_nicotine_level_options(): array
    {
        return ['0mg', '3mg', '6mg', '12mg', '20mg', '50mg'];
    }
}

if (! function_exists('product_uses_compliance_fields')) {
    function product_uses_compliance_fields(string $category): bool
    {
        $slug = strtolower(trim($category));

        return in_array($slug, ['pods', 'pod', 'disposable', 'e-liquid', 'eliquid'], true)
            || str_contains($slug, 'liquid');
    }
}

if (! function_exists('normalize_product_expiration_date')) {
    function normalize_product_expiration_date($value): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }
}

if (! function_exists('product_is_expired')) {
    function product_is_expired($expiresAt): bool
    {
        $normalized = normalize_product_expiration_date($expiresAt);
        if ($normalized === null) {
            return false;
        }

        return $normalized < date('Y-m-d');
    }
}

if (! function_exists('product_expiration_status')) {
    /**
     * @return array{is_expired: bool, label: string, days_until: ?int}
     */
    function product_expiration_status($expiresAt): array
    {
        $normalized = normalize_product_expiration_date($expiresAt);
        if ($normalized === null) {
            return [
                'is_expired' => false,
                'label' => 'N/A',
                'days_until' => null,
            ];
        }

        $today = date('Y-m-d');
        $todayTs = strtotime($today);
        $expiryTs = strtotime($normalized);
        $daysUntil = ($todayTs !== false && $expiryTs !== false)
            ? (int) floor(($expiryTs - $todayTs) / 86400)
            : null;

        if ($normalized < $today) {
            return [
                'is_expired' => true,
                'label' => format_product_expiration_date($normalized),
                'days_until' => $daysUntil,
            ];
        }

        return [
            'is_expired' => false,
            'label' => format_product_expiration_date($normalized),
            'days_until' => $daysUntil,
        ];
    }
}

if (! function_exists('format_product_expiration_date')) {
    function format_product_expiration_date($value): string
    {
        $normalized = normalize_product_expiration_date($value);
        if ($normalized === null) {
            return 'N/A';
        }

        $timestamp = strtotime($normalized);

        return $timestamp !== false ? date('M j, Y', $timestamp) : 'N/A';
    }
}

if (! function_exists('is_device_category')) {
    function is_device_category(string $category): bool
    {
        $slug = strtolower(trim($category));
        $compact = preg_replace('/[^a-z0-9]+/', '', $slug) ?? $slug;

        return in_array($slug, ['device', 'devices'], true) || $compact === 'device';
    }
}

if (! function_exists('product_device_type_options')) {
    /**
     * @return array<string, string> slug => label
     */
    function product_device_type_options(): array
    {
        return [
            'battery' => 'Battery Only',
            'pod_mod' => 'Pod Mod',
            'aio' => 'AIO (All-in-One)',
            'pod_device' => 'Pod Device',
            'mod' => 'Mod',
        ];
    }
}

if (! function_exists('product_charging_port_options')) {
    /**
     * @return list<string>
     */
    function product_charging_port_options(): array
    {
        return ['USB-C', 'Micro-USB', 'Magnetic', 'Lightning', 'Other'];
    }
}

if (! function_exists('normalize_device_type')) {
    function normalize_device_type($value): ?string
    {
        $value = strtolower(trim((string) ($value ?? '')));
        $options = product_device_type_options();

        if ($value === '') {
            return null;
        }

        if (isset($options[$value])) {
            return $value;
        }

        foreach ($options as $slug => $label) {
            if (strcasecmp($label, (string) ($value ?? '')) === 0) {
                return $slug;
            }
        }

        return null;
    }
}

if (! function_exists('format_device_type_label')) {
    function format_device_type_label($value): string
    {
        $slug = normalize_device_type($value);
        if ($slug === null) {
            return 'N/A';
        }

        return product_device_type_options()[$slug] ?? 'N/A';
    }
}

if (! function_exists('device_type_field_visibility')) {
    /**
     * Which device specification inputs apply for each device type.
     *
     * @return array{battery_capacity: bool, wattage_range: bool, charging_port: bool, compatibility: bool}
     */
    function device_type_field_visibility(string $deviceType): array
    {
        $deviceType = normalize_device_type($deviceType) ?? '';

        return match ($deviceType) {
            'battery' => [
                'battery_capacity' => true,
                'wattage_range' => false,
                'charging_port' => true,
                'compatibility' => true,
            ],
            'pod_mod', 'aio' => [
                'battery_capacity' => true,
                'wattage_range' => true,
                'charging_port' => true,
                'compatibility' => true,
            ],
            'pod_device' => [
                'battery_capacity' => true,
                'wattage_range' => false,
                'charging_port' => true,
                'compatibility' => true,
            ],
            'mod' => [
                'battery_capacity' => false,
                'wattage_range' => true,
                'charging_port' => true,
                'compatibility' => true,
            ],
            default => [
                'battery_capacity' => false,
                'wattage_range' => false,
                'charging_port' => false,
                'compatibility' => false,
            ],
        };
    }
}

if (! function_exists('format_device_wattage_range')) {
    function format_device_wattage_range($value): string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : 'N/A';
    }
}

if (! function_exists('format_device_charging_port')) {
    function format_device_charging_port($value): string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : 'N/A';
    }
}

if (! function_exists('format_device_compatibility')) {
    function format_device_compatibility($value): string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : 'N/A';
    }
}

if (! function_exists('is_disposable_category')) {
    function is_disposable_category(string $category): bool
    {
        $slug = strtolower(trim($category));
        $compact = preg_replace('/[^a-z0-9]+/', '', $slug) ?? $slug;

        return $slug === 'disposable' || $slug === 'disposables' || $compact === 'disposable';
    }
}

if (! function_exists('format_product_battery_capacity')) {
    function format_product_battery_capacity($value): string
    {
        $amount = (int) ($value ?? 0);

        return $amount > 0 ? number_format($amount) . 'mAh' : 'N/A';
    }
}

if (! function_exists('format_product_eliquid_capacity')) {
    function format_product_eliquid_capacity($value): string
    {
        $amount = (int) ($value ?? 0);

        return $amount > 0 ? $amount . 'ML' : 'N/A';
    }
}

if (! function_exists('format_product_nicotine_level')) {
    function format_product_nicotine_level($value): string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : 'N/A';
    }
}
