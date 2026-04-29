<?php

declare(strict_types=1);

namespace App\Helper;

class ValidatorHelper
{
    protected array $errors = [];
    protected array $data = [];
    protected array $rules = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        $this->validate();
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(): string
    {
        $this->validate();
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                return $error;
            }
        }
        return '';
    }

    protected function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            foreach ($rules as $rule) {
                $this->validateRule($field, $rule);
            }
        }
    }

    protected function validateRule(string $field, string $rule): void
    {
        $value = $this->data[$field] ?? null;

        if (strpos($rule, ':') !== false) {
            [$ruleName, $param] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $param = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = "{$field} 是必填项";
                }
                break;
            case 'max':
                if (strlen((string) $value) > (int) $param) {
                    $this->errors[$field][] = "{$field} 不能超过 {$param} 个字符";
                }
                break;
            case 'min':
                if (strlen((string) $value) < (int) $param) {
                    $this->errors[$field][] = "{$field} 至少需要 {$param} 个字符";
                }
                break;
            case 'integer':
                if (!is_numeric($value) || (string) (int) $value !== (string) $value) {
                    $this->errors[$field][] = "{$field} 必须是整数";
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "{$field} 不是有效的邮箱地址";
                }
                break;
            case 'sometimes':
                if (!isset($this->data[$field])) {
                    return;
                }
                break;
        }
    }
}
