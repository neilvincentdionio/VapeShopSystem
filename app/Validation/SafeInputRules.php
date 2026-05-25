<?php

namespace App\Validation;

/**
 * Rejects special characters while allowing ñ/Ñ on text fields.
 */
class SafeInputRules
{
    public function safe_person_name(?string $value, ?string $fields = null, array $data = [], ?string &$error = null, ?string $field = null): bool
    {
        return $this->validateType($value, 'person_name', $error);
    }

    public function safe_text(?string $value, ?string $fields = null, array $data = [], ?string &$error = null, ?string $field = null): bool
    {
        return $this->validateType($value, 'text', $error);
    }

    public function safe_location(?string $value, ?string $fields = null, array $data = [], ?string &$error = null, ?string $field = null): bool
    {
        return $this->validateType($value, 'location', $error);
    }

    public function safe_address(?string $value, ?string $fields = null, array $data = [], ?string &$error = null, ?string $field = null): bool
    {
        return $this->validateType($value, 'address', $error);
    }

    public function safe_description(?string $value, ?string $fields = null, array $data = [], ?string &$error = null, ?string $field = null): bool
    {
        return $this->validateType($value, 'description', $error);
    }

    public function safe_reference(?string $value, ?string $fields = null, array $data = [], ?string &$error = null, ?string $field = null): bool
    {
        return $this->validateType($value, 'reference', $error);
    }

    public function safe_postal_code(?string $value, ?string $fields = null, array $data = [], ?string &$error = null, ?string $field = null): bool
    {
        return $this->validateType($value, 'postal_code', $error);
    }

    public function safe_nicotine(?string $value, ?string $fields = null, array $data = [], ?string &$error = null, ?string $field = null): bool
    {
        return $this->validateType($value, 'nicotine', $error);
    }

    public function safe_spec(?string $value, ?string $fields = null, array $data = [], ?string &$error = null, ?string $field = null): bool
    {
        return $this->validateType($value, 'spec', $error);
    }

    private function validateType(?string $value, string $type, ?string &$error): bool
    {
        helper('input_validation');

        $value = trim((string) $value);
        if ($value === '') {
            return true;
        }

        if (matches_safe_input($value, $type)) {
            return true;
        }

        $messages = safe_input_messages();
        $error = $messages[$type] ?? 'This field contains unsupported characters.';

        return false;
    }
}
