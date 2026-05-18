<?php

namespace App\Services;

class DocumentTemplateCompatibilityService
{
    public function analyze(string $documentType, array $bindingIndex): array
    {
        $dictionary = DocumentVariableService::getDictionary($documentType);
        $dictionaryMeta = $this->buildDictionaryMeta($dictionary);

        $missingDataPaths = [];
        foreach ((array) ($bindingIndex['data_paths'] ?? []) as $path) {
            $path = trim((string) $path);
            if ($path === '') {
                continue;
            }

            if (! $this->isKnownPath($path, $dictionaryMeta)) {
                $missingDataPaths[] = $path;
            }
        }

        $tableIssues = [];
        foreach ((array) ($bindingIndex['table_bindings'] ?? []) as $table) {
            if (! is_array($table)) {
                continue;
            }

            $dataKey = trim((string) ($table['data_key'] ?? ''));
            $columns = array_values(array_filter(array_map('strval', (array) ($table['columns'] ?? []))));

            if ($dataKey === '') {
                $tableIssues[] = [
                    'type' => 'missing_table_data_key',
                    'message' => 'Table binding is missing data_key.',
                ];
                continue;
            }

            if (! isset($dictionaryMeta['arrays'][$dataKey])) {
                $tableIssues[] = [
                    'type' => 'unknown_table_data_key',
                    'data_key' => $dataKey,
                    'message' => "Table data_key [{$dataKey}] is not defined as an array variable in dictionary.",
                ];
                continue;
            }

            $allowedChildKeys = $dictionaryMeta['arrays'][$dataKey];
            $unknownColumns = array_values(array_diff($columns, $allowedChildKeys));

            if ($unknownColumns !== []) {
                $tableIssues[] = [
                    'type' => 'unknown_table_columns',
                    'data_key' => $dataKey,
                    'columns' => $unknownColumns,
                    'message' => 'Table columns contain keys not present in dictionary child fields.',
                ];
            }
        }

        $listIssues = [];
        foreach ((array) ($bindingIndex['list_paths'] ?? []) as $path) {
            $path = trim((string) $path);
            if ($path === '') {
                continue;
            }

            if (! $this->isKnownArrayPath($path, $dictionaryMeta)) {
                $listIssues[] = [
                    'type' => 'unknown_list_data_key',
                    'data_key' => $path,
                    'message' => "List data_key [{$path}] is not in dictionary.",
                ];
            }
        }

        $issues = [
            'missing_data_paths' => array_values(array_unique($missingDataPaths)),
            'table_issues' => $tableIssues,
            'list_issues' => $listIssues,
        ];

        $issueCount = count($issues['missing_data_paths']) + count($tableIssues) + count($listIssues);

        return [
            'status' => $issueCount === 0 ? 'compatible' : 'warning',
            'issue_count' => $issueCount,
            'issues' => $issues,
        ];
    }

    private function buildDictionaryMeta(array $dictionary): array
    {
        $keys = [];
        $arrays = [];

        foreach ($dictionary as $groupEntries) {
            if (! is_array($groupEntries)) {
                continue;
            }

            foreach ($groupEntries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $key = trim((string) ($entry['key'] ?? ''));
                if ($key === '') {
                    continue;
                }

                $keys[$key] = true;

                if (($entry['is_array'] ?? false) && is_array($entry['children'] ?? null)) {
                    $arrays[$key] = [];
                    foreach ($entry['children'] as $child) {
                        $childKey = trim((string) ($child['key'] ?? ''));
                        if ($childKey === '') {
                            continue;
                        }

                        $arrays[$key][] = $this->normalizeChildKey($childKey);
                    }

                    $arrays[$key] = array_values(array_unique($arrays[$key]));
                }
            }
        }

        return [
            'keys' => $keys,
            'arrays' => $arrays,
        ];
    }

    private function normalizeChildKey(string $key): string
    {
        // Dictionary children sometimes come as `service.title` while table row keys are often `title`.
        if (str_contains($key, '.')) {
            $parts = explode('.', $key);
            return (string) end($parts);
        }

        return $key;
    }

    private function isKnownPath(string $path, array $dictionaryMeta): bool
    {
        if (isset($dictionaryMeta['keys'][$path])) {
            return true;
        }

        if ($this->isKnownArrayPath($path, $dictionaryMeta)) {
            return true;
        }

        // Allow service field namespace variants for runtime-captured detail snapshots.
        if (
            str_starts_with($path, 'service.fields.')
            || str_starts_with($path, 'services.fields.')
            || str_starts_with($path, 'service_instances.fields.')
        ) {
            return true;
        }

        // Allow known array child path patterns such as invoice.line_items.description.
        foreach ($dictionaryMeta['arrays'] as $arrayKey => $childKeys) {
            if (str_starts_with($path, $arrayKey.'.')) {
                $suffix = substr($path, strlen($arrayKey) + 1);
                if (in_array($suffix, $childKeys, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isKnownArrayPath(string $path, array $dictionaryMeta): bool
    {
        if (isset($dictionaryMeta['arrays'][$path]) || isset($dictionaryMeta['keys'][$path])) {
            return true;
        }

        foreach ($this->compatibilityArrayAliases() as $alias => $canonical) {
            if ($path === $alias && isset($dictionaryMeta['arrays'][$canonical])) {
                return true;
            }

            if (str_starts_with($path, $alias . '.')) {
                $canonicalPath = $canonical . substr($path, strlen($alias));

                if (isset($dictionaryMeta['keys'][$canonicalPath])) {
                    return true;
                }

                foreach ($dictionaryMeta['arrays'] as $arrayKey => $childKeys) {
                    if (str_starts_with($canonicalPath, $arrayKey . '.')) {
                        $suffix = substr($canonicalPath, strlen($arrayKey) + 1);
                        if (in_array($suffix, $childKeys, true)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    private function compatibilityArrayAliases(): array
    {
        return [
            'services' => 'service_rows',
            'service_instances' => 'service_rows',
            'operation' => 'service_record',
        ];
    }
}
