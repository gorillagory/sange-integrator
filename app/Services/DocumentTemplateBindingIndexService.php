<?php

namespace App\Services;

class DocumentTemplateBindingIndexService
{
    public function extract(array $layoutVector): array
    {
        $dataPaths = [];
        $placeholders = [];
        $listBindings = [];
        $tableBindings = [];

        $zones = ['header', 'body', 'footer'];
        foreach ($zones as $zone) {
            $this->walkNodes(
                is_array($layoutVector[$zone] ?? null) ? $layoutVector[$zone] : [],
                $dataPaths,
                $placeholders,
                $listBindings,
                $tableBindings
            );
        }

        $dataPaths = array_values(array_unique(array_filter($dataPaths)));
        $placeholders = array_values(array_unique(array_filter($placeholders)));
        $listBindings = array_values(array_unique(array_filter($listBindings)));

        $tableBindings = array_values(array_map(function (array $entry) {
            $entry['data_key'] = trim((string) ($entry['data_key'] ?? ''));
            $entry['columns'] = array_values(array_unique(array_filter(array_map('strval', $entry['columns'] ?? []))));
            return $entry;
        }, $tableBindings));

        return [
            'data_paths' => $dataPaths,
            'placeholder_paths' => $placeholders,
            'list_paths' => $listBindings,
            'table_bindings' => $tableBindings,
            'binding_count' => count($dataPaths),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function walkNodes(
        array $nodes,
        array &$dataPaths,
        array &$placeholders,
        array &$listBindings,
        array &$tableBindings
    ): void {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $type = (string) ($node['type'] ?? '');
            $dataKey = trim((string) ($node['data_key'] ?? ''));

            if ($dataKey !== '') {
                $dataPaths[] = $dataKey;
            }

            if ($type === 'text') {
                $content = (string) ($node['content'] ?? '');
                preg_match_all('/\{\{\s*([a-zA-Z0-9._]+)\s*\}\}/', $content, $matches);
                foreach ($matches[1] ?? [] as $path) {
                    $path = trim((string) $path);
                    if ($path === '') {
                        continue;
                    }
                    $dataPaths[] = $path;
                    $placeholders[] = $path;
                }
            }

            if ($type === 'list' && $dataKey !== '') {
                $listBindings[] = $dataKey;
            }

            if ($type === 'table') {
                $columns = [];
                foreach ((array) ($node['columns'] ?? []) as $column) {
                    $key = trim((string) ($column['key'] ?? ''));
                    if ($key !== '') {
                        $columns[] = $key;
                    }
                }

                $tableBindings[] = [
                    'data_key' => $dataKey,
                    'columns' => $columns,
                ];
            }

            if ($type === 'row') {
                foreach ((array) ($node['columns'] ?? []) as $column) {
                    $this->walkNodes(
                        is_array($column['blocks'] ?? null) ? $column['blocks'] : [],
                        $dataPaths,
                        $placeholders,
                        $listBindings,
                        $tableBindings
                    );
                }
            }
        }
    }
}

