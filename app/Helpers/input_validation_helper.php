<?php

/**
 * Shared input patterns: letters (including ñ/Ñ), numbers, and limited punctuation only.
 */

if (! function_exists('safe_input_patterns')) {
    /**
     * @return array<string, string> Regex patterns (with delimiters) keyed by type.
     */
    function safe_input_patterns(): array
    {
        return [
            'person_name' => '/^[a-zA-ZñÑ0-9\s\-.\'’]+$/u',
            'text' => '/^[a-zA-ZñÑ0-9\s\-.\'’]+$/u',
            'location' => '/^[a-zA-ZñÑ0-9\s\-.\'’]+$/u',
            'address' => '/^[a-zA-ZñÑ0-9\s\-.\'#,\/]+$/u',
            'description' => '/^[a-zA-ZñÑ0-9\s\-.\'”,:;!?()]+$/u',
            'reference' => '/^[a-zA-Z0-9\-]+$/u',
            'postal_code' => '/^[a-zA-Z0-9\s\-]+$/u',
            'nicotine' => '/^[a-zA-Z0-9\s\-%\.]+$/u',
            'spec' => '/^[a-zA-Z0-9\s\-.\'\/]+$/u',
        ];
    }
}

if (! function_exists('safe_input_messages')) {
    /**
     * @return array<string, string>
     */
    function safe_input_messages(): array
    {
        return [
            'person_name' => 'Only letters (including ñ/Ñ), numbers, spaces, hyphens, apostrophes, and periods are allowed.',
            'text' => 'Only letters (including ñ/Ñ), numbers, spaces, hyphens, apostrophes, and periods are allowed.',
            'location' => 'Only letters (including ñ/Ñ), numbers, spaces, hyphens, apostrophes, and periods are allowed.',
            'address' => 'Only letters (including ñ/Ñ), numbers, spaces, and common address punctuation (# , / - . \') are allowed.',
            'description' => 'Description contains unsupported characters.',
            'reference' => 'Only letters, numbers, and hyphens are allowed.',
            'postal_code' => 'Postal code can only contain letters, numbers, spaces, and hyphens.',
            'nicotine' => 'Nicotine level contains unsupported characters.',
            'spec' => 'This field contains unsupported characters.',
        ];
    }
}

if (! function_exists('matches_safe_input')) {
    function matches_safe_input(?string $value, string $type): bool
    {
        $value = trim((string) $value);
        if ($value === '') {
            return true;
        }

        $patterns = safe_input_patterns();

        return isset($patterns[$type]) && preg_match($patterns[$type], $value) === 1;
    }
}

if (! function_exists('sanitize_safe_text')) {
    function sanitize_safe_text(string $value, string $type = 'text'): string
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', strip_tags($value)));

        $allowed = match ($type) {
            'person_name', 'text', 'location' => 'a-zA-ZñÑ0-9\s\-.\'’',
            'address' => 'a-zA-ZñÑ0-9\s\-.\'#,\/',
            'description' => 'a-zA-ZñÑ0-9\s\-.\'”,:;!?()',
            'reference' => 'a-zA-Z0-9\-',
            'postal_code' => 'a-zA-Z0-9\s\-',
            'nicotine' => 'a-zA-Z0-9\s\-%\.',
            'spec' => 'a-zA-Z0-9\s\-.\'\/',
            default => 'a-zA-ZñÑ0-9\s\-.\'’',
        };

        return trim((string) preg_replace('/[^' . $allowed . ']+/u', '', $value));
    }
}

if (! function_exists('validation_rules_safe_person_name')) {
    function validation_rules_safe_person_name(bool $required = true, int $min = 3, int $max = 255): string
    {
        $prefix = $required ? 'required' : 'permit_empty';

        return $prefix . '|min_length[' . $min . ']|max_length[' . $max . ']|safe_person_name';
    }
}

if (! function_exists('validation_rules_safe_text')) {
    function validation_rules_safe_text(bool $required = true, int $min = 1, int $max = 255): string
    {
        $prefix = $required ? 'required' : 'permit_empty';
        $rules = $prefix . '|max_length[' . $max . ']|safe_text';
        if ($required && $min > 0) {
            $rules = 'required|min_length[' . $min . ']|max_length[' . $max . ']|safe_text';
        }

        return $rules;
    }
}

if (! function_exists('product_text_validation_rules')) {
    /**
     * @return array<string, string>
     */
    function product_text_validation_rules(): array
    {
        return [
            'name' => 'required|min_length[3]|max_length[255]|safe_text',
            'brand' => 'required|max_length[100]|safe_text',
            'flavor' => 'permit_empty|max_length[100]|safe_text',
            'nicotine_level' => 'permit_empty|max_length[20]|safe_nicotine',
            'device_type' => 'permit_empty|max_length[50]|safe_spec',
            'wattage_range' => 'permit_empty|max_length[50]|safe_spec',
            'charging_port' => 'permit_empty|max_length[30]|safe_spec',
            'compatibility' => 'permit_empty|max_length[255]|safe_spec',
            'description' => 'permit_empty|max_length[5000]|safe_description',
        ];
    }
}

if (! function_exists('record_text_validation_rules')) {
    /**
     * @return array<string, string>
     */
    function record_text_validation_rules(): array
    {
        return [
            'reference_number' => 'required|min_length[3]|max_length[100]|safe_reference',
            'title' => 'required|min_length[3]|max_length[255]|safe_text',
            'description' => 'permit_empty|max_length[1000]|safe_description',
            'notes' => 'permit_empty|max_length[1000]|safe_description',
        ];
    }
}
