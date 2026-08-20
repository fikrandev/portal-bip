<?php
/**
 * Portal BIP - Input Validator
 * 
 * Server-side input validation with readable error messages.
 */

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Create a new Validator instance
     */
    public static function make(array $data): self
    {
        return new self($data);
    }

    /**
     * Validate required field
     */
    public function required(string $field, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field][] = "{$label} wajib diisi.";
        }
        return $this;
    }

    /**
     * Validate email format
     */
    public function email(string $field, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "{$label} harus berupa alamat email yang valid.";
        }
        return $this;
    }

    /**
     * Validate minimum length
     */
    public function minLength(string $field, int $min, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!empty($this->data[$field]) && mb_strlen($this->data[$field]) < $min) {
            $this->errors[$field][] = "{$label} minimal {$min} karakter.";
        }
        return $this;
    }

    /**
     * Validate maximum length
     */
    public function maxLength(string $field, int $max, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!empty($this->data[$field]) && mb_strlen($this->data[$field]) > $max) {
            $this->errors[$field][] = "{$label} maksimal {$max} karakter.";
        }
        return $this;
    }

    /**
     * Validate uniqueness in database
     */
    public function unique(string $field, string $table, string $column, ?int $exceptId = null, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!empty($this->data[$field])) {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as cnt FROM `{$table}` WHERE `{$column}` = ?";
            $params = [$this->data[$field]];

            if ($exceptId !== null) {
                $sql .= " AND id != ?";
                $params[] = $exceptId;
            }

            $result = $db->find($sql, $params);
            if (($result['cnt'] ?? 0) > 0) {
                $this->errors[$field][] = "{$label} sudah digunakan.";
            }
        }
        return $this;
    }

    /**
     * Validate field matches another field
     */
    public function confirmed(string $field, string $confirmField, string $label = ''): self
    {
        $label = $label ?: $field;
        if (($this->data[$field] ?? '') !== ($this->data[$confirmField] ?? '')) {
            $this->errors[$field][] = "Konfirmasi {$label} tidak cocok.";
        }
        return $this;
    }

    /**
     * Validate numeric value
     */
    public function numeric(string $field, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = "{$label} harus berupa angka.";
        }
        return $this;
    }

    /**
     * Validate alphanumeric (and underscores)
     */
    public function alphanumeric(string $field, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!empty($this->data[$field]) && !preg_match('/^[a-zA-Z0-9_]+$/', $this->data[$field])) {
            $this->errors[$field][] = "{$label} hanya boleh huruf, angka, dan underscore.";
        }
        return $this;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for a field
     */
    public function error(string $field): string
    {
        return $this->errors[$field][0] ?? '';
    }

    /**
     * Get all errors as flat array
     */
    public function allErrors(): array
    {
        $flat = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $flat[] = $error;
            }
        }
        return $flat;
    }

    /**
     * Get sanitized value
     */
    public function getValue(string $field, $default = ''): string
    {
        $value = $this->data[$field] ?? $default;
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}
