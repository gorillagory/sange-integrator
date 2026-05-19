<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SchemaVector;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ServiceRecordPayloadValidator
{
    public function __construct(
        private readonly SchemaValidationRuleCompiler $ruleCompiler,
    ) {
    }

    public function validateAndNormalize(array $rows, Company $company, ?string $serviceGroupKey = null): array
    {
        $normalized = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                $errors["rows.{$index}"][] = 'Invalid service record row payload.';
                continue;
            }

            $vector = $this->resolveVector($row, $company, $serviceGroupKey);

            if (! $vector) {
                $errors["rows.{$index}.schema_vector_id"][] = 'The selected schema vector is no longer available for this service group. Please remove this row and add it again from the current vector list.';
                continue;
            }

            $inputCode = trim((string) ($row['service_code'] ?? ''));
            $resolvedCode = (string) ($vector->service_code ?: $vector->service_type);
            if ($inputCode !== '' && $resolvedCode !== '' && $inputCode !== $resolvedCode) {
                $errors["rows.{$index}.service_code"][] = 'Provided service_code does not match resolved schema vector.';
                continue;
            }

            $details = is_array($row['service_details'] ?? null) ? $row['service_details'] : [];
            $detailsExtra = is_array($row['service_details_extra'] ?? null) ? $row['service_details_extra'] : [];
            $vectorPayload = is_array($vector->schema_payload ?? null) ? $vector->schema_payload : [];
            $fields = is_array($vectorPayload['fields'] ?? null) ? $vectorPayload['fields'] : [];
            $compiled = $this->ruleCompiler->compile($fields);

            $validator = Validator::make($details, $compiled['rules'], [], $compiled['attributes']);

            $knownKeys = $compiled['known_keys'];
            $unknownKeys = array_values(array_diff(array_keys($details), $knownKeys));
            if ($unknownKeys !== []) {
                $validator->after(function ($validator) use ($unknownKeys) {
                    $validator->errors()->add(
                        'details_unknown_keys',
                        'Unknown governed keys in service_details: '.implode(', ', $unknownKeys).'. Use service_details_extra for ad hoc values.'
                    );
                });
            }

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $fieldErrors) {
                    $targetField = $field === 'details_unknown_keys'
                        ? "rows.{$index}.service_details"
                        : "rows.{$index}.service_details.{$field}";

                    foreach ($fieldErrors as $fieldError) {
                        $errors[$targetField][] = $fieldError;
                    }
                }

                continue;
            }

            $vectorCode = $resolvedCode;
            $vectorType = (string) ($vector->service_type ?: $vectorCode);
            $vectorName = (string) ($vector->service_name ?: $vector->display_name ?: 'Service');
            $vectorVersion = (int) ($vector->version ?? 1);

            $normalized[] = array_merge($row, [
                'schema_vector_id' => $vector->id,
                'service_code' => $vectorCode,
                'service_type' => $vectorType,
                'service_name' => $vectorName,
                'schema_version' => $vectorVersion,
                'unit_name' => $row['unit_name'] ?? $this->resolveUnitName($vector),
                'service_details' => $details,
                'service_details_extra' => $detailsExtra,
            ]);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $normalized;
    }

    private function resolveVector(array $row, Company $company, ?string $serviceGroupKey): ?SchemaVector
    {
        $vectorId = $row['schema_vector_id'] ?? $row['service_schema_id'] ?? null;
        if (! is_numeric($vectorId)) {
            return null;
        }

        return SchemaVector::query()
            ->where('industry', $company->industry)
            ->when($serviceGroupKey, fn ($query) => $query->where(function ($scope) use ($serviceGroupKey) {
                $scope->where('service_group_key', $serviceGroupKey)
                    ->orWhereNull('service_group_key')
                    ->orWhere('service_group_key', '');
            }))
            ->whereKey((int) $vectorId)
            ->first();
    }

    private function resolveUnitName(SchemaVector $vector): ?string
    {
        $payload = is_array($vector->schema_payload ?? null) ? $vector->schema_payload : [];
        $pricingUnits = array_values(array_filter(array_map(
            fn ($unit) => is_string($unit) ? trim($unit) : '',
            is_array($payload['pricing_units'] ?? null) ? $payload['pricing_units'] : []
        )));
        $unit = $payload['commercial']['unit'] ?? $payload['unit'] ?? ($pricingUnits[0] ?? null);

        return is_string($unit) && trim($unit) !== '' ? trim($unit) : null;
    }
}
