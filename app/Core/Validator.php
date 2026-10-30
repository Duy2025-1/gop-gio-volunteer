<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label): self
    {
        $val = trim((string)($this->data[$field] ?? ''));
        if ($val === '') {
            $this->errors[$field] = "{$label} không được để trống.";
        }
        return $this;
    }

    public function email(string $field, string $label = 'Email'): self
    {
        $val = trim((string)($this->data[$field] ?? ''));
        if ($val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} không đúng định dạng hợp lệ.";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label): self
    {
        $val = (string)($this->data[$field] ?? '');
        if ($val !== '' && mb_strlen($val) < $min) {
            $this->errors[$field] = "{$label} phải có ít nhất {$min} ký tự.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        $val = (string)($this->data[$field] ?? '');
        if ($val !== '' && mb_strlen($val) > $max) {
            $this->errors[$field] = "{$label} không được vượt quá {$max} ký tự.";
        }
        return $this;
    }

    public function phone(string $field, string $label = 'Số điện thoại'): self
    {
        $val = trim((string)($this->data[$field] ?? ''));
        if ($val !== '' && !preg_match('/^(0|\+84)[0-9]{8,10}$/', preg_replace('/\s+/', '', $val))) {
            $this->errors[$field] = "{$label} không hợp lệ (ví dụ: 0912345678).";
        }
        return $this;
    }

    public function matches(string $field, string $matchField, string $label): self
    {
        $val1 = (string)($this->data[$field] ?? '');
        $val2 = (string)($this->data[$matchField] ?? '');
        if ($val1 !== $val2) {
            $this->errors[$field] = "{$label} không trùng khớp.";
        }
        return $this;
    }

    public function numericMin(string $field, float $min, string $label): self
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && (!is_numeric($val) || (float)$val < $min)) {
            $this->errors[$field] = "{$label} phải lớn hơn hoặc bằng {$min}.";
        }
        return $this;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return reset($this->errors) ?: 'Dữ liệu không hợp lệ.';
    }
}
