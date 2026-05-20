<?php

if (! function_exists('stock_units_for_category')) {
    /**
     * @return array{singular: string, plural: string}
     */
    function stock_units_for_category(string $category): array
    {
        $slug = strtolower(trim($category));
        $slug = preg_replace('/[^a-z0-9]+/', '', $slug) ?? $slug;

        if (str_contains($slug, 'eliquid') || str_contains($slug, 'liquid')) {
            return ['singular' => 'bottle', 'plural' => 'bottles'];
        }

        if (str_contains($slug, 'disposable')) {
            return ['singular' => 'piece', 'plural' => 'pieces'];
        }

        if (str_contains($slug, 'pod')) {
            return ['singular' => 'pod', 'plural' => 'pods'];
        }

        if (str_contains($slug, 'device')) {
            return ['singular' => 'piece', 'plural' => 'pieces'];
        }

        return ['singular' => 'unit', 'plural' => 'units'];
    }
}

if (! function_exists('stock_unit_label')) {
    function stock_unit_label(string $category, int $quantity = 2): string
    {
        $units = stock_units_for_category($category);

        return $quantity === 1 ? $units['singular'] : $units['plural'];
    }
}

if (! function_exists('format_stock_display')) {
    function format_stock_display(int $quantity, string $category, bool $useGrouping = true): string
    {
        $number = $useGrouping ? number_format($quantity) : (string) $quantity;

        return trim($number . ' ' . stock_unit_label($category, $quantity));
    }
}

if (! function_exists('is_eliquid_category')) {
    function is_eliquid_category(string $category): bool
    {
        $slug = strtolower(trim($category));
        $slug = preg_replace('/[^a-z0-9]+/', '', $slug) ?? $slug;

        return str_contains($slug, 'eliquid') || str_contains($slug, 'liquid');
    }
}

if (! function_exists('product_spec_column_label')) {
    function product_spec_column_label(string $category): string
    {
        return is_eliquid_category($category) ? 'Capacity' : 'Puffs';
    }
}

if (! function_exists('format_product_spec_value')) {
    function format_product_spec_value(?int $value, string $category): string
    {
        if ($value === null || $value <= 0) {
            return 'N/A';
        }

        return is_eliquid_category($category)
            ? $value . 'ML'
            : number_format($value);
    }
}

if (! function_exists('resolve_product_line_spec_values')) {
    /**
     * Collapse merged variant rows to one display value per product line.
     *
     * @param list<int> $values
     * @return list<int>
     */
    function resolve_product_line_spec_values(array $values, string $category): array
    {
        $values = array_values(array_filter(
            array_map(static fn ($value) => (int) $value, $values),
            static fn (int $value) => $value > 0
        ));

        if ($values === []) {
            return [];
        }

        if (! is_eliquid_category($category)) {
            sort($values);

            return array_values(array_unique($values));
        }

        if (in_array(10, $values, true)) {
            return [10];
        }

        return [min($values)];
    }
}

if (! function_exists('format_product_spec_values')) {
    /**
     * @param list<int> $values
     */
    function format_product_spec_values(array $values, string $category): string
    {
        $values = resolve_product_line_spec_values($values, $category);

        if ($values === []) {
            return 'N/A';
        }

        if (is_eliquid_category($category)) {
            return implode(', ', array_map(static fn (int $value) => $value . 'ML', $values));
        }

        return implode(', ', array_map(static fn (int $value) => number_format($value), $values));
    }
}
