<?php

namespace App\Services;

use Illuminate\Validation\Rule;

class SchemaValidationRuleCompiler
{
    public function compile(array $fields): array
    {
        $rules = [];
        $attributes = [];
        $knownKeys = [];

        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $key = trim((string) ($field['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $knownKeys[] = $key;
            $label = (string) ($field['label'] ?? $key);
            $attributes[$key] = $label;

            $isArray = (bool) ($field['is_array'] ?? false);
            $required = in_array('required', (array) ($field['rules'] ?? []), true);

            if ($isArray) {
                $rules[$key] = array_values(array_filter([
                    $required ? 'required' : 'nullable',
                    'array',
                    $required ? 'min:1' : null,
                ]));

                $itemRules = array_merge(
                    [$required ? 'required' : 'nullable'],
                    $this->typeRules((string) ($field['type'] ?? 'string')),
                    $this->fieldConstraints($field, true)
                );

                if (is_array($field['allowed_values'] ?? null) && ($field['allowed_values'] ?? []) !== []) {
                    $itemRules[] = Rule::in($field['allowed_values']);
                }

                $rules[$key.'.*'] = array_values(array_unique($itemRules, SORT_REGULAR));
                $attributes[$key.'.*'] = $label;
                continue;
            }

            $scalarRules = array_merge(
                [$required ? 'required' : 'nullable'],
                $this->typeRules((string) ($field['type'] ?? 'string')),
                $this->fieldConstraints($field, false)
            );

            if (is_array($field['allowed_values'] ?? null) && ($field['allowed_values'] ?? []) !== []) {
                $scalarRules[] = Rule::in($field['allowed_values']);
            }

            $rules[$key] = array_values(array_unique($scalarRules, SORT_REGULAR));
        }

        return [
            'rules' => $rules,
            'attributes' => $attributes,
            'known_keys' => array_values(array_unique($knownKeys)),
        ];
    }

    private function typeRules(string $type): array
    {
        return match (strtolower($type)) {
            'number', 'integer' => ['integer'],
            'float', 'decimal' => ['numeric'],
            'email' => ['email'],
            'date' => ['date'],
            'datetime' => ['date'],
            'time' => ['regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'file' => ['string'],
            default => ['string'],
        };
    }

    private function fieldConstraints(array $field, bool $isItemRule): array
    {
        $rules = [];

        $min = $field['min'] ?? null;
        $max = $field['max'] ?? null;
        $pattern = $field['pattern'] ?? null;

        if (is_numeric($min)) {
            $rules[] = 'min:'.$min;
        }

        if (is_numeric($max)) {
            $rules[] = 'max:'.$max;
        }

        if (is_string($pattern) && trim($pattern) !== '') {
            $rules[] = 'regex:'.$pattern;
        }

        $ruleList = (array) ($field['rules'] ?? []);
        $passthrough = [
            'email',
            'url',
            'integer',
            'numeric',
            'date',
            'boolean',
            'alpha',
            'alpha_num',
            'alpha_dash',
            'sometimes',
        ];

        foreach ($ruleList as $rule) {
            if (! is_string($rule)) {
                continue;
            }

            $normalized = trim($rule);
            if ($normalized === '' || $normalized === 'required') {
                continue;
            }

            if (str_starts_with($normalized, 'min:') || str_starts_with($normalized, 'max:') || str_starts_with($normalized, 'regex:')) {
                $rules[] = $normalized;
                continue;
            }

            if (in_array($normalized, $passthrough, true)) {
                $rules[] = $normalized;
            }
        }

        // Keep item-level rules predictable for arrays (no "sometimes" noise per element).
        if ($isItemRule) {
            $rules = array_values(array_filter($rules, fn ($rule) => $rule !== 'sometimes'));
        }

        return $rules;
    }
}

